<?php

namespace App\Controllers\Staff;

use App\Controllers\ProtectedController;
use App\Models\LetterAttachmentModel;
use App\Models\LetterModel;
use App\Models\LetterReplyModel;
use App\Models\NotificationModel;
use App\Models\ReplyAttachmentModel;
use App\Models\UserModel;
use App\Models\UserProfileModel;

class LetterController extends ProtectedController
{
    public function index()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $letterModel = new LetterModel();
        $userModel = new UserModel();
        $profileModel = new UserProfileModel();
        
        $letters = $letterModel->orderBy('created_at', 'DESC')->findAll();
        
        // Ambil data user dan profile untuk setiap surat
        foreach ($letters as &$letter) {
            $user = $userModel->find($letter['user_id']);
            $profile = $profileModel->find($letter['user_id']);
            
            // Gunakan nama_lengkap jika ada, jika tidak gunakan username
            $letter['sender_name'] = ($profile && !empty($profile['nama_lengkap'])) 
                ? $profile['nama_lengkap'] 
                : ($user['username'] ?? 'Unknown');
        }

        return view('Staff/letters/index', ['letters' => $letters]);
    }

    public function api()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $letterModel = new LetterModel();
        $userModel = new UserModel();
        $profileModel = new UserProfileModel();
        $db = \Config\Database::connect();
        
        // Get DataTables parameters
        $draw = $this->request->getPost('draw') ?? 1;
        $start = $this->request->getPost('start') ?? 0;
        $length = $this->request->getPost('length') ?? 10;
        $search = $this->request->getPost('search')['value'] ?? '';
        $searchCustom = $this->request->getPost('search_custom') ?? '';
        $orderColumn = $this->request->getPost('order')[0]['column'] ?? 3;
        $orderDir = $this->request->getPost('order')[0]['dir'] ?? 'desc';
        
        // Get filter parameters
        $dateStart = $this->request->getPost('date_start') ?? '';
        $dateEnd = $this->request->getPost('date_end') ?? '';
        $tipeSuratFilter = $this->request->getPost('tipe_surat_filter') ?? '';
        $statusFilter = $this->request->getPost('status_filter') ?? '';
        
        // Use search_custom if provided, otherwise use default search
        $searchValue = !empty($searchCustom) ? $searchCustom : $search;
        
        // Column mapping
        $columns = ['kode_unik', 'judul_perihal', 'tipe_surat', 'sender_name', 'status', 'sent_at'];
        $orderBy = $columns[$orderColumn] ?? 'sent_at';
        
        // Get total records
        $recordsTotal = $letterModel->countAllResults(false);
        
        // Build base query with join
        $builder = $db->table('letters l')
            ->select('l.*, 
                COALESCE(up.nama_lengkap, u.username, "Unknown") as sender_name,
                up.jenis_kelamin')
            ->join('users u', 'u.id = l.user_id', 'left')
            ->join('user_profiles up', 'up.user_id = l.user_id', 'left');
        
        // Apply date filter
        if (!empty($dateStart)) {
            $builder->where('DATE(l.sent_at) >=', $dateStart);
        }
        if (!empty($dateEnd)) {
            $builder->where('DATE(l.sent_at) <=', $dateEnd);
        }
        
        // Apply tipe surat filter
        if (!empty($tipeSuratFilter)) {
            $builder->where('l.tipe_surat', $tipeSuratFilter);
        }
        
        // Apply status filter
        if (!empty($statusFilter)) {
            $builder->where('l.status', $statusFilter);
        }
        
        // Apply search filter
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('l.kode_unik', $searchValue)
                ->orLike('l.judul_perihal', $searchValue)
                ->orLike('l.status', $searchValue)
                ->orLike('up.nama_lengkap', $searchValue)
                ->orLike('u.username', $searchValue)
                ->groupEnd();
        }
        
        // Get filtered count
        $recordsFiltered = $builder->countAllResults(false);
        
        // Apply ordering
        if ($orderBy === 'sender_name') {
            $builder->orderBy('COALESCE(up.nama_lengkap, u.username, "Unknown")', strtoupper($orderDir));
        } elseif ($orderBy === 'kode_unik') {
            $builder->orderBy('l.kode_unik', strtoupper($orderDir));
        } else {
            $builder->orderBy('l.' . $orderBy, strtoupper($orderDir));
        }
        
        // Apply pagination
        $builder->limit($length, $start);
        
        $letters = $builder->get()->getResultArray();
        
        // Format data
        $data = [];
        foreach ($letters as $letter) {
            $data[] = [
                'id' => $letter['id'],
                'kode_unik' => esc($letter['kode_unik'] ?? '-'),
                'judul_perihal' => esc($letter['judul_perihal']),
                'tipe_surat' => esc($letter['tipe_surat'] ?? '-'),
                'sender_name' => esc($letter['sender_name']),
                'status' => esc($letter['status']),
                'sent_at' => date('d M Y H:i', strtotime($letter['sent_at'])),
            ];
        }

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
            csrf_token() => csrf_hash()
        ]);
    }

    public function show($id)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $letterModel      = new LetterModel();
        $attachmentModel  = new LetterAttachmentModel();
        $replyModel       = new LetterReplyModel();
        $replyAttachModel = new ReplyAttachmentModel();
        $userModel        = new UserModel();
        $profileModel     = new UserProfileModel();

        $letter = $letterModel->find($id);
        if (!$letter) {
            return redirect()->to('/staff/surat')->with('error', 'Surat tidak ditemukan.');
        }

        // Fetch sender profile
        $senderProfile = $profileModel->find($letter['user_id']);
        $letter['sender_gender'] = $senderProfile['jenis_kelamin'] ?? null;

        if ($letter['status'] === 'Menunggu') {
            $letterModel->update($id, ['status' => 'Dibaca', 'read_at' => date('Y-m-d H:i:s'), 'assigned_staff_id' => $this->currentUser['id']]);
            $letter['status']  = 'Dibaca';
            $letter['read_at'] = date('Y-m-d H:i:s');
            
            // Ambil nama staff (nama_lengkap dari profile, fallback ke username)
            $staffProfile = $profileModel->find($this->currentUser['id']);
            $staffName = ($staffProfile && !empty($staffProfile['nama_lengkap'])) 
                ? $staffProfile['nama_lengkap'] 
                : $this->currentUser['username'];
            
            // Buat notifikasi untuk user bahwa suratnya telah dibaca
            $notifModel = new NotificationModel();
            $notifModel->insert([
                'user_id'           => $letter['user_id'],
                'type'              => 'letter_read',
                'title'             => 'Surat Anda telah dibaca',
                'message'           => 'Surat Anda: ' . $letter['judul_perihal'] . ' telah dibaca oleh ' . $staffName,
                'related_letter_id' => $id,
                'is_read'           => 0,
                'created_at'        => date('Y-m-d H:i:s'),
            ]);
            
            // Kirim email notifikasi ke user
            $user = $userModel->find($letter['user_id']);
            if ($user) {
                $emailService = new \App\Libraries\EmailService();
                $letterUrl = base_url('/user/surat/' . $id);
                $emailService->sendNotification(
                    $user['email'],
                    $user['username'],
                    'Surat Anda telah dibaca',
                    'Surat Anda: ' . $letter['judul_perihal'] . ' telah dibaca oleh ' . $staffName,
                    'letter_read',
                    $letterUrl,
                    $letter['judul_perihal'],
                    $letter['tipe_surat'] ?? null
                );
            }
        }

        $replies = $replyModel->where('letter_id', $id)->findAll();
        $replyAttachments = [];
        $replyProfiles = [];
        
        foreach ($replies as $reply) {
            $replyAttachments[$reply['id']] = $replyAttachModel->where('reply_id', $reply['id'])->findAll();
            
            // Get staff profile for reply
            if (!empty($reply['staff_id'])) {
                $staffProfile = $profileModel->find($reply['staff_id']);
                $staffUser = $userModel->find($reply['staff_id']);
                
                $replyProfiles[$reply['id']] = [
                    'foto_profil' => $staffProfile['foto_profil'] ?? null,
                    'nama_lengkap' => $staffProfile['nama_lengkap'] ?? null,
                    'username' => $staffUser['username'] ?? 'Staff',
                ];
            }
        }

        return view('Staff/letters/detail', [
            'letter'           => $letter,
            'user'             => $senderProfile,
            'attachments'      => $attachmentModel->where('letter_id', $id)->findAll(),
            'replies'          => $replies,
            'replyAttachments' => $replyAttachments,
            'replyProfiles'    => $replyProfiles,
            'currentStaffId'   => $this->currentUser['id'],
        ]);
    }



    public function reply($id)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $letterModel = new LetterModel();
        $letter      = $letterModel->find($id);

        if (!$letter) {
            return redirect()->to('/staff/surat')->with('error', 'Surat tidak ditemukan.');
        }

        // Get action from form (accept, reject, or reply)
        $action = $this->request->getPost('action');
        $replyText = $this->request->getPost('reply_text');

        // Logic for Initial Decision (Menunggu/Dibaca)
        if (in_array($letter['status'], ['Menunggu', 'Dibaca'])) {
            if ($action === 'accept') {
                // Update status to Diterima
                $letterModel->update($id, [
                    'status' => 'Diterima',
                    'assigned_staff_id' => $this->currentUser['id'],
                    'replied_at' => date('Y-m-d H:i:s')
                ]);
                
                // Set notification title/type
                $notifType = 'letter_accepted';
                $notifTitle = 'Surat Anda diterima';
                $emailSubject = 'Surat Anda diterima';
                
            } elseif ($action === 'reject') {
                // Update status to Ditolak
                $letterModel->update($id, [
                    'status' => 'Ditolak',
                    'assigned_staff_id' => $this->currentUser['id'],
                    'replied_at' => date('Y-m-d H:i:s')
                ]);
                
                // Set notification title/type
                $notifType = 'letter_rejected';
                $notifTitle = 'Surat Anda ditolak';
                $emailSubject = 'Surat Anda ditolak';
                
            } else {
                return redirect()->back()->withInput()->with('error', 'Silakan pilih Terima atau Tolak.');
            }
        } 
        // Logic for Existing Conversation (Diterima)
        elseif ($letter['status'] === 'Diterima') {
            // Normal reply
            $letterModel->update($id, [
                'replied_at' => date('Y-m-d H:i:s'),
                'assigned_staff_id' => $this->currentUser['id']
            ]);
            
            $notifType = 'reply';
            $notifTitle = 'Balasan baru untuk surat Anda';
            $emailSubject = 'Balasan baru untuk surat Anda';
        } 
        // Logic for Rejected (Should not happen via UI)
        elseif ($letter['status'] === 'Ditolak') {
            return redirect()->to('/staff/surat/' . $id)->with('error', 'Surat yang ditolak tidak dapat dibalas lagi.');
        }

        // Save the reply
        $replyModel  = new LetterReplyModel();
        $replyId     = $replyModel->insert([
            'letter_id' => $id,
            'staff_id'  => $this->currentUser['id'],
            'reply_text'=> $replyText,
        ], true);

        $this->handleReplyAttachments($replyId);

        // Send Notification
        $profileModel = new UserProfileModel();
        $staffProfile = $profileModel->find($this->currentUser['id']);
        $staffName = ($staffProfile && !empty($staffProfile['nama_lengkap'])) 
            ? $staffProfile['nama_lengkap'] 
            : $this->currentUser['username'];

        $notifModel = new NotificationModel();
        $notifModel->insert([
            'user_id'           => $letter['user_id'],
            'type'              => $notifType,
            'title'             => $notifTitle,
            'message'           => $notifTitle . ' oleh ' . $staffName . "\n\n" . substr($replyText, 0, 50) . '...',
            'related_letter_id' => $id,
            'related_reply_id'  => $replyId,
            'is_read'           => 0,
            'created_at'        => date('Y-m-d H:i:s'),
        ]);
        
        // Send Email
        $userModel = new UserModel();
        $user = $userModel->find($letter['user_id']);
        if ($user) {
            $emailService = new \App\Libraries\EmailService();
            $letterUrl = base_url('/user/surat/' . $id);
            $emailService->sendNotification(
                $user['email'],
                $user['username'],
                $emailSubject,
                $notifTitle . ' oleh ' . $staffName . "<br><br>Isi Balasan:<br>" . nl2br($replyText),
                $notifType,
                $letterUrl,
                $letter['judul_perihal'],
                $letter['tipe_surat'] ?? null
            );
        }

        return redirect()->to('/staff/surat/' . $id)->with('success', 'Balasan berhasil dikirim.');
    }

    public function delete($id)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $letterModel = new LetterModel();
        $attachmentModel = new LetterAttachmentModel();
        $replyModel = new LetterReplyModel();
        $replyAttachModel = new ReplyAttachmentModel();
        $notificationModel = new NotificationModel();

        $letter = $letterModel->find($id);
        if (!$letter) {
            return redirect()->to('/staff/surat')->with('error', 'Surat tidak ditemukan.');
        }

        // Hapus lampiran surat
        $attachments = $attachmentModel->where('letter_id', $id)->findAll();
        foreach ($attachments as $att) {
            $filePath = FCPATH . ltrim($att['file_path'], '/');
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }
        $attachmentModel->where('letter_id', $id)->delete();

        // Hapus balasan dan lampiran balasan
        $replies = $replyModel->where('letter_id', $id)->findAll();
        foreach ($replies as $reply) {
            // Hapus lampiran balasan
            $replyAttachments = $replyAttachModel->where('reply_id', $reply['id'])->findAll();
            foreach ($replyAttachments as $replyAtt) {
                $filePath = FCPATH . ltrim($replyAtt['file_path'], '/');
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }
            $replyAttachModel->where('reply_id', $reply['id'])->delete();
        }
        $replyModel->where('letter_id', $id)->delete();

        // Hapus notifikasi terkait
        $notificationModel->where('related_letter_id', $id)->delete();

        // Hapus surat
        $letterModel->delete($id);

        return redirect()->to('/staff/surat')->with('success', 'Surat berhasil dihapus.');
    }

    public function deleteReply($letterId, $replyId)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $letterModel = new LetterModel();
        $replyModel = new LetterReplyModel();
        $replyAttachModel = new ReplyAttachmentModel();

        $letter = $letterModel->find($letterId);
        $reply = $replyModel->find($replyId);

        if (!$letter || !$reply || $reply['letter_id'] != $letterId) {
            return redirect()->to('/staff/surat/' . $letterId)->with('error', 'Balasan tidak ditemukan.');
        }

        // Cek apakah balasan adalah milik staff yang login
        if ($reply['staff_id'] != $this->currentUser['id']) {
            return redirect()->to('/staff/surat/' . $letterId)->with('error', 'Anda hanya bisa menghapus balasan Anda sendiri.');
        }

        // Hapus lampiran balasan
        $attachments = $replyAttachModel->where('reply_id', $replyId)->findAll();
        foreach ($attachments as $att) {
            $filePath = FCPATH . ltrim($att['file_path'], '/');
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }
        $replyAttachModel->where('reply_id', $replyId)->delete();

        // Hapus balasan
        $replyModel->delete($replyId);

        // Cek jumlah balasan yang tersisa
        $remainingReplies = $replyModel->where('letter_id', $letterId)->countAllResults();

        // Jika tidak ada balasan lagi, ubah status menjadi "Dibaca" (tanpa notifikasi)
        if ($remainingReplies == 0) {
            $letterModel->update($letterId, [
                'status' => 'Dibaca',
                'replied_at' => null
            ]);
        }

        return redirect()->to('/staff/surat/' . $letterId)->with('success', 'Balasan dihapus.');
    }

    private function handleReplyAttachments(int $replyId): void
    {
        $files = $this->request->getFileMultiple('reply_attachments');
        if (!$files) {
            return;
        }

        $uploadPath      = FCPATH . 'uploads/replies';
        $replyAttachModel= new ReplyAttachmentModel();
        $this->ensureUploadPath($uploadPath);

        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'webp'];

        foreach ($files as $file) {
            if (!$file->isValid()) {
                continue;
            }

            $ext = strtolower($file->getClientExtension());
            if (!in_array($ext, $allowedExtensions)) {
                session()->setFlashdata('error', 'Salah satu lampiran balasan memiliki format yang tidak didukung. Lampiran tersebut tidak disimpan.');
                continue;
            }

            $newName = $file->getRandomName();
            $file->move($uploadPath, $newName);

            $replyAttachModel->insert([
                'reply_id'      => $replyId,
                'file_path'     => 'uploads/replies/' . $newName,
                'original_name' => $file->getClientName(),
                'mime_type'     => $file->getClientMimeType(),
                'file_size'     => $file->getSize(),
            ]);
        }
    }
}


