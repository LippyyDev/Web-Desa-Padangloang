<?php

namespace App\Controllers\Staff;

use App\Controllers\ProtectedController;
use App\Models\DesaProfileModel;
use App\Models\GalleryAlbumModel;
use App\Models\GalleryMediaModel;
use App\Models\NewsMediaModel;
use App\Models\NewsModel;
use App\Models\PerangkatDesaModel;
use App\Models\ProjectMediaModel;
use App\Models\ProjectModel;

class ContentController extends ProtectedController
{
    public function desaProfile()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $profileModel = new DesaProfileModel();

        return view('Staff/desa_profile', [
            'profile' => $profileModel->first(),
        ]);
    }

    public function updateDesaProfile()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $mapsUrl = $this->request->getPost('maps_url');
        // Extract URL from iframe tag if user pasted full HTML
        if ($mapsUrl && preg_match('/src=["\']([^"\']+)["\']/', $mapsUrl, $matches)) {
            $mapsUrl = $matches[1];
        }
        // Clean any remaining HTML tags
        $mapsUrl = strip_tags($mapsUrl);
        $mapsUrl = trim($mapsUrl);

        $profileModel = new DesaProfileModel();
        $data         = [
            'visi'                => $this->request->getPost('visi'),
            'misi'                => $this->request->getPost('misi'),
            'jumlah_penduduk'     => $this->request->getPost('jumlah_penduduk'),
            'jumlah_kk'           => $this->request->getPost('jumlah_kk'),
            'penduduk_sementara'  => $this->request->getPost('penduduk_sementara'),
            'jumlah_laki'         => $this->request->getPost('jumlah_laki'),
            'jumlah_perempuan'    => $this->request->getPost('jumlah_perempuan'),
            'mutasi_penduduk'     => $this->request->getPost('mutasi_penduduk'),
            'kontak_wa'           => $this->request->getPost('kontak_wa'),
            'kontak_email'        => $this->request->getPost('kontak_email'),
            'alamat_kantor'       => $this->request->getPost('alamat_kantor'),
            'maps_url'            => $mapsUrl,
            'deskripsi_lokasi'    => $this->request->getPost('deskripsi_lokasi'),
            'updated_by'          => $this->currentUser['id'],
        ];

        $profile = $profileModel->first();
        if ($profile) {
            $profileModel->update($profile['id'], $data);
        } else {
            $profileModel->insert($data);
        }

        return redirect()->back()->with('success', 'Profil desa disimpan.');
    }

    public function gallery()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        return view('Staff/gallery/index');
    }

    public function galleryApi()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $albumModel = new GalleryAlbumModel();
        
        $page = (int)($this->request->getGet('page') ?? 1);
        $limit = (int)($this->request->getGet('limit') ?? 12);
        $search = $this->request->getGet('search') ?? '';
        $offset = ($page - 1) * $limit;

        // Apply search filter
        if (!empty($search)) {
            $albumModel->groupStart()
                ->like('nama_album', $search)
                ->orLike('deskripsi', $search)
                ->groupEnd();
        }

        // Get total count with search
        $total = $albumModel->countAllResults(false);
        
        // Reset and apply search again for data fetch
        $albumModel = new GalleryAlbumModel();
        if (!empty($search)) {
            $albumModel->groupStart()
                ->like('nama_album', $search)
                ->orLike('deskripsi', $search)
                ->groupEnd();
        }

        $albums = $albumModel->orderBy('tanggal_waktu', 'DESC')
            ->findAll($limit, $offset);

        $data = [];
        foreach ($albums as $album) {
            $data[] = [
                'id' => $album['id'],
                'nama_album' => $album['nama_album'],
                'deskripsi' => $album['deskripsi'] ?? '',
                'tanggal_waktu' => date('d M Y', strtotime($album['tanggal_waktu'])),
                'thumbnail' => $album['thumbnail'] ? base_url($album['thumbnail']) : 'https://via.placeholder.com/600x360?text=Album',
            ];
        }

        return $this->response->setJSON([
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit),
            'search' => $search,
        ]);
    }

    public function createGallery()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        return view('Staff/gallery/create');
    }

    public function editGallery($id)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $albumModel = new GalleryAlbumModel();
        $album      = $albumModel->find($id);
        if (!$album) {
            return redirect()->to('/staff/galeri')->with('error', 'Album tidak ditemukan.');
        }

        $mediaModel = new GalleryMediaModel();
        $media      = $mediaModel->where('album_id', $id)->findAll();
        foreach ($media as &$m) {
            if ($m['media_type'] === 'video_link') {
                $m['embed_url'] = $this->toEmbedUrl($m['media_path']);
            }
        }

        return view('Staff/gallery/edit', [
            'album' => $album,
            'media' => $media,
        ]);
    }

    public function storeGallery()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $albumModel = new GalleryAlbumModel();
        $data       = [
            'nama_album'   => $this->request->getPost('nama_album'),
            'deskripsi'    => $this->request->getPost('deskripsi'),
            'tanggal_waktu'=> $this->request->getPost('tanggal_waktu') ?: date('Y-m-d H:i:s'),
            'created_by'   => $this->currentUser['id'],
        ];

        $thumb = $this->request->getFile('thumbnail');
        if ($thumb && $thumb->isValid()) {
            // Tolak format .GIF
            $extension = $thumb->getClientExtension();
            if (strtolower($extension) === 'gif') {
                return redirect()->back()->with('error', 'Format file .GIF tidak diperbolehkan.');
            }

            $path = FCPATH . 'uploads/gallery';
            $this->ensureUploadPath($path);
            
            // Upload file sementara
            $tempName = $thumb->getRandomName();
            $thumb->move($path, $tempName);
            $tempPath = $path . '/' . $tempName;
            
            // Convert ke WebP
            $image = \Config\Services::image();
            $webpName = pathinfo($tempName, PATHINFO_FILENAME) . '.webp';
            $webpPath = $path . '/' . $webpName;
            
            try {
                $image->withFile($tempPath)
                    ->convert(IMAGETYPE_WEBP)
                    ->save($webpPath, 85); // Quality 85
                
                // Hapus file sementara
                if (file_exists($tempPath)) {
                    @unlink($tempPath);
                }
                
                $data['thumbnail'] = 'uploads/gallery/' . $webpName;
            } catch (\Exception $e) {
                // Jika konversi gagal, hapus file sementara
                if (file_exists($tempPath)) {
                    @unlink($tempPath);
                }
                return redirect()->back()->with('error', 'Gagal memproses gambar thumbnail. Pastikan file adalah gambar yang valid.');
            }
        }

        $albumId = $albumModel->insert($data, true);
        $this->saveGalleryMedia($albumId);

        return redirect()->back()->with('success', 'Album galeri disimpan.');
    }

    public function updateGallery($id)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $albumModel = new GalleryAlbumModel();
        $album      = $albumModel->find($id);
        if (!$album) {
            return redirect()->back()->with('error', 'Album tidak ditemukan.');
        }

        $data = [
            'nama_album'    => $this->request->getPost('nama_album'),
            'deskripsi'     => $this->request->getPost('deskripsi'),
            'tanggal_waktu' => $this->request->getPost('tanggal_waktu') ?: $album['tanggal_waktu'],
        ];

        $thumb = $this->request->getFile('thumbnail');
        if ($thumb && $thumb->isValid()) {
            $path = FCPATH . 'uploads/gallery';
            $this->ensureUploadPath($path);
            $name = $thumb->getRandomName();
            $thumb->move($path, $name);
            $data['thumbnail'] = 'uploads/gallery/' . $name;
        }

        $albumModel->update($id, $data);
        $this->saveGalleryMedia($id);

        return redirect()->back()->with('success', 'Album diperbarui.');
    }

    public function deleteGallery($id)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $albumModel = new GalleryAlbumModel();
        $mediaModel = new GalleryMediaModel();
        $album      = $albumModel->find($id);

        if ($album) {
            $mediaList = $mediaModel->where('album_id', $id)->findAll();
            foreach ($mediaList as $media) {
                $file = FCPATH . ltrim($media['media_path'], '/');
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            $mediaModel->where('album_id', $id)->delete();
            $albumModel->delete($id);
        }

        return redirect()->back()->with('success', 'Album dihapus.');
    }

    private function saveGalleryMedia(int $albumId): void
    {
        $files = $this->request->getFileMultiple('media');
        if (!$files) {
            return;
        }

        $mediaModel = new GalleryMediaModel();
        $path       = FCPATH . 'uploads/gallery';
        $this->ensureUploadPath($path);
        $image      = \Config\Services::image();

        foreach ($files as $file) {
            if (!$file->isValid()) {
                continue;
            }
            
            // Tolak format .GIF
            $extension = $file->getClientExtension();
            if (strtolower($extension) === 'gif') {
                continue; // Skip file GIF
            }
            
            // Upload file sementara
            $tempName = $file->getRandomName();
            $file->move($path, $tempName);
            $tempPath = $path . '/' . $tempName;
            
            // Convert ke WebP
            $webpName = pathinfo($tempName, PATHINFO_FILENAME) . '.webp';
            $webpPath = $path . '/' . $webpName;
            
            try {
                $image->withFile($tempPath)
                    ->convert(IMAGETYPE_WEBP)
                    ->save($webpPath, 85); // Quality 85
                
                // Hapus file sementara
                if (file_exists($tempPath)) {
                    @unlink($tempPath);
                }
                
                $mediaModel->insert([
                    'album_id'   => $albumId,
                    'media_type' => 'foto',
                    'media_path' => 'uploads/gallery/' . $webpName,
                ]);
            } catch (\Exception $e) {
                // Jika konversi gagal, hapus file sementara
                if (file_exists($tempPath)) {
                    @unlink($tempPath);
                }
                // Skip file yang gagal dikonversi
                continue;
            }
        }

        $videoLinks = $this->request->getPost('video_links');
        if (is_array($videoLinks)) {
            foreach ($videoLinks as $block) {
                $lines = preg_split('/\r\n|\r|\n/', (string) $block);
                foreach ($lines as $link) {
                    if (trim($link) === '') {
                        continue;
                    }
                    $mediaModel->insert([
                        'album_id'   => $albumId,
                        'media_type' => 'video_link',
                        'media_path' => trim($link),
                    ]);
                }
            }
        }
    }

    public function deleteGalleryMedia($mediaId)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $mediaModel = new GalleryMediaModel();
        $media      = $mediaModel->find($mediaId);
        if ($media) {
            if ($media['media_type'] === 'foto') {
                $file = FCPATH . ltrim($media['media_path'], '/');
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            $albumId = $media['album_id'];
            $mediaModel->delete($mediaId);
            return redirect()->to('/staff/galeri/' . $albumId . '/edit')->with('success', 'Media dihapus.');
        }

        return redirect()->back()->with('error', 'Media tidak ditemukan.');
    }

    public function news()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        return view('Staff/news/index');
    }

    public function newsApi()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $newsModel = new NewsModel();
        
        $page = (int)($this->request->getGet('page') ?? 1);
        $limit = (int)($this->request->getGet('limit') ?? 12);
        $search = $this->request->getGet('search') ?? '';
        $offset = ($page - 1) * $limit;

        // Apply search filter
        if (!empty($search)) {
            $newsModel->groupStart()
                ->like('judul', $search)
                ->orLike('isi', $search)
                ->groupEnd();
        }

        // Get total count with search
        $total = $newsModel->countAllResults(false);
        
        // Reset and apply search again for data fetch
        $newsModel = new NewsModel();
        if (!empty($search)) {
            $newsModel->groupStart()
                ->like('judul', $search)
                ->orLike('isi', $search)
                ->groupEnd();
        }

        $news = $newsModel->orderBy('tanggal_waktu', 'DESC')
            ->findAll($limit, $offset);

        $data = [];
        foreach ($news as $item) {
            $data[] = [
                'id' => $item['id'],
                'judul' => $item['judul'],
                'isi' => strip_tags($item['isi']),
                'tanggal_waktu' => date('d M Y', strtotime($item['tanggal_waktu'])),
                'thumbnail' => $item['thumbnail'] ? base_url($item['thumbnail']) : 'https://via.placeholder.com/600x360?text=Berita',
            ];
        }

        return $this->response->setJSON([
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit),
            'search' => $search,
        ]);
    }

    public function createNews()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        return view('Staff/news/create');
    }

    public function editNews($id)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $newsModel = new NewsModel();
        $news      = $newsModel->find($id);
        if (!$news) {
            return redirect()->to('/staff/berita')->with('error', 'Berita tidak ditemukan.');
        }

        $mediaModel = new NewsMediaModel();
        $media      = $mediaModel->where('news_id', $id)->findAll();
        foreach ($media as &$m) {
            if (isset($m['media_type']) && $m['media_type'] === 'video_link') {
                $m['embed_url'] = $this->toEmbedUrl($m['media_path']);
            }
        }

        return view('Staff/news/edit', [
            'item'  => $news,
            'media' => $media,
        ]);
    }

    public function storeNews()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $newsModel = new NewsModel();
        $data      = [
            'judul'         => $this->request->getPost('judul'),
            'tanggal_waktu' => $this->request->getPost('tanggal_waktu') ?: date('Y-m-d H:i:s'),
            'isi'           => $this->request->getPost('isi'),
            'created_by'    => $this->currentUser['id'],
        ];

        $thumb = $this->request->getFile('thumbnail');
        if ($thumb && $thumb->isValid()) {
            $path = FCPATH . 'uploads/news';
            $this->ensureUploadPath($path);
            $name = $thumb->getRandomName();
            $thumb->move($path, $name);
            $data['thumbnail'] = 'uploads/news/' . $name;
        }

        $newsId = $newsModel->insert($data, true);
        $this->saveNewsMedia($newsId);

        return redirect()->back()->with('success', 'Berita disimpan.');
    }

    public function updateNews($id)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $newsModel = new NewsModel();
        $news      = $newsModel->find($id);

        if (!$news) {
            return redirect()->back()->with('error', 'Berita tidak ditemukan.');
        }

        $data = [
            'judul'         => $this->request->getPost('judul'),
            'tanggal_waktu' => $this->request->getPost('tanggal_waktu') ?: $news['tanggal_waktu'],
            'isi'           => $this->request->getPost('isi'),
        ];

        $thumb = $this->request->getFile('thumbnail');
        if ($thumb && $thumb->isValid()) {
            $path = FCPATH . 'uploads/news';
            $this->ensureUploadPath($path);
            $name = $thumb->getRandomName();
            $thumb->move($path, $name);
            $data['thumbnail'] = 'uploads/news/' . $name;
        }

        $newsModel->update($id, $data);
        $this->saveNewsMedia($id);

        return redirect()->back()->with('success', 'Berita diperbarui.');
    }

    public function deleteNews($id)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $newsModel = new NewsModel();
        $mediaModel= new NewsMediaModel();
        $news      = $newsModel->find($id);

        if ($news) {
            $mediaList = $mediaModel->where('news_id', $id)->findAll();
            foreach ($mediaList as $media) {
                if (isset($media['media_type']) && $media['media_type'] === 'foto') {
                    $file = FCPATH . ltrim($media['media_path'], '/');
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
            }
            $mediaModel->where('news_id', $id)->delete();
            $newsModel->delete($id);
        }

        return redirect()->back()->with('success', 'Berita dihapus.');
    }

    private function saveNewsMedia(int $newsId): void
    {
        $files = $this->request->getFileMultiple('media');
        if ($files) {
            $mediaModel = new NewsMediaModel();
            $path       = FCPATH . 'uploads/news';
            $this->ensureUploadPath($path);

            foreach ($files as $file) {
                if (!$file->isValid()) {
                    continue;
                }
                $name = $file->getRandomName();
                $file->move($path, $name);
                $mediaModel->insert([
                    'news_id'    => $newsId,
                    'media_type' => 'foto',
                    'media_path' => 'uploads/news/' . $name,
                ]);
            }
        }

        $videoLinks = $this->request->getPost('video_links');
        if (is_array($videoLinks)) {
            $mediaModel = new NewsMediaModel();
            foreach ($videoLinks as $block) {
                $lines = preg_split('/\r\n|\r|\n/', (string) $block);
                foreach ($lines as $link) {
                    if (trim($link) === '') {
                        continue;
                    }
                    $mediaModel->insert([
                        'news_id'    => $newsId,
                        'media_type' => 'video_link',
                        'media_path' => trim($link),
                    ]);
                }
            }
        }
    }

    public function deleteNewsMedia($mediaId)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $mediaModel = new NewsMediaModel();
        $media      = $mediaModel->find($mediaId);
        if ($media) {
            if (isset($media['media_type']) && $media['media_type'] === 'foto') {
                $file = FCPATH . ltrim($media['media_path'], '/');
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            $newsId = $media['news_id'];
            $mediaModel->delete($mediaId);
            return redirect()->to('/staff/berita/' . $newsId . '/edit')->with('success', 'Media dihapus.');
        }

        return redirect()->back()->with('error', 'Media tidak ditemukan.');
    }

    public function projects()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        return view('Staff/projects/index');
    }

    public function projectsApi()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $projectModel = new ProjectModel();
        
        $page = (int)($this->request->getGet('page') ?? 1);
        $limit = (int)($this->request->getGet('limit') ?? 12);
        $search = $this->request->getGet('search') ?? '';
        $offset = ($page - 1) * $limit;

        // Apply search filter
        if (!empty($search)) {
            $projectModel->groupStart()
                ->like('judul', $search)
                ->orLike('deskripsi', $search)
                ->orLike('status', $search)
                ->groupEnd();
        }

        // Get total count with search
        $total = $projectModel->countAllResults(false);
        
        // Reset and apply search again for data fetch
        $projectModel = new ProjectModel();
        if (!empty($search)) {
            $projectModel->groupStart()
                ->like('judul', $search)
                ->orLike('deskripsi', $search)
                ->orLike('status', $search)
                ->groupEnd();
        }

        $projects = $projectModel->orderBy('tanggal_waktu', 'DESC')
            ->findAll($limit, $offset);

        $data = [];
        foreach ($projects as $project) {
            $data[] = [
                'id' => $project['id'],
                'judul' => $project['judul'],
                'deskripsi' => strip_tags($project['deskripsi'] ?? ''),
                'status' => $project['status'],
                'anggaran' => $project['anggaran'] ?? 0,
                'tanggal_waktu' => date('d M Y', strtotime($project['tanggal_waktu'])),
                'thumbnail' => $project['thumbnail'] ? base_url($project['thumbnail']) : 'https://via.placeholder.com/600x360?text=Project',
            ];
        }

        return $this->response->setJSON([
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit),
            'search' => $search,
        ]);
    }

    public function createProject()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        return view('Staff/projects/create');
    }

    public function editProject($id)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $projectModel = new ProjectModel();
        $project      = $projectModel->find($id);
        if (!$project) {
            return redirect()->to('/staff/projects')->with('error', 'Project tidak ditemukan.');
        }

        $mediaModel = new ProjectMediaModel();
        $media      = $mediaModel->where('project_id', $id)->findAll();
        foreach ($media as &$m) {
            if (isset($m['media_type']) && $m['media_type'] === 'video_link') {
                $m['embed_url'] = $this->toEmbedUrl($m['media_path']);
            }
        }

        return view('Staff/projects/edit', [
            'project' => $project,
            'media'   => $media,
        ]);
    }

    public function storeProject()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $projectModel = new ProjectModel();
        $data         = [
            'judul'         => $this->request->getPost('judul'),
            'tanggal_waktu' => $this->request->getPost('tanggal_waktu') ?: date('Y-m-d H:i:s'),
            'deskripsi'     => $this->request->getPost('deskripsi'),
            'anggaran'      => $this->request->getPost('anggaran') ?: 0,
            'status'        => $this->request->getPost('status') ?: 'Perencanaan',
            'created_by'    => $this->currentUser['id'],
        ];

        $thumb = $this->request->getFile('thumbnail');
        if ($thumb && $thumb->isValid()) {
            $path = FCPATH . 'uploads/projects';
            $this->ensureUploadPath($path);
            $name = $thumb->getRandomName();
            $thumb->move($path, $name);
            $data['thumbnail'] = 'uploads/projects/' . $name;
        }

        $projectId = $projectModel->insert($data, true);
        $this->saveProjectMedia($projectId);

        return redirect()->back()->with('success', 'Project disimpan.');
    }

    public function updateProject($id)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $projectModel = new ProjectModel();
        $project      = $projectModel->find($id);

        if (!$project) {
            return redirect()->back()->with('error', 'Project tidak ditemukan.');
        }

        $data = [
            'judul'         => $this->request->getPost('judul'),
            'tanggal_waktu' => $this->request->getPost('tanggal_waktu') ?: $project['tanggal_waktu'],
            'deskripsi'     => $this->request->getPost('deskripsi'),
            'anggaran'      => $this->request->getPost('anggaran') ?: $project['anggaran'],
            'status'        => $this->request->getPost('status') ?: $project['status'],
        ];

        $thumb = $this->request->getFile('thumbnail');
        if ($thumb && $thumb->isValid()) {
            $path = FCPATH . 'uploads/projects';
            $this->ensureUploadPath($path);
            $name = $thumb->getRandomName();
            $thumb->move($path, $name);
            $data['thumbnail'] = 'uploads/projects/' . $name;
        }

        $projectModel->update($id, $data);
        $this->saveProjectMedia($id);

        return redirect()->back()->with('success', 'Project diperbarui.');
    }

    public function deleteProject($id)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $projectModel = new ProjectModel();
        $mediaModel   = new ProjectMediaModel();
        $project      = $projectModel->find($id);

        if ($project) {
            $mediaList = $mediaModel->where('project_id', $id)->findAll();
            foreach ($mediaList as $media) {
                if (isset($media['media_type']) && $media['media_type'] === 'foto') {
                    $file = FCPATH . ltrim($media['media_path'], '/');
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
            }
            $mediaModel->where('project_id', $id)->delete();
            $projectModel->delete($id);
        }

        return redirect()->back()->with('success', 'Project dihapus.');
    }

    private function saveProjectMedia(int $projectId): void
    {
        $files = $this->request->getFileMultiple('media');
        if ($files) {
            $mediaModel = new ProjectMediaModel();
            $path       = FCPATH . 'uploads/projects';
            $this->ensureUploadPath($path);

            foreach ($files as $file) {
                if (!$file->isValid()) {
                    continue;
                }
                $name = $file->getRandomName();
                $file->move($path, $name);
                $mediaModel->insert([
                    'project_id' => $projectId,
                    'media_type' => 'foto',
                    'media_path' => 'uploads/projects/' . $name,
                ]);
            }
        }

        $videoLinks = $this->request->getPost('video_links');
        if (is_array($videoLinks)) {
            $mediaModel = new ProjectMediaModel();
            foreach ($videoLinks as $block) {
                $lines = preg_split('/\r\n|\r|\n/', (string) $block);
                foreach ($lines as $link) {
                    if (trim($link) === '') {
                        continue;
                    }
                    $mediaModel->insert([
                        'project_id' => $projectId,
                        'media_type' => 'video_link',
                        'media_path' => trim($link),
                    ]);
                }
            }
        }
    }

    public function deleteProjectMedia($mediaId)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $mediaModel = new ProjectMediaModel();
        $media      = $mediaModel->find($mediaId);
        if ($media) {
            if (isset($media['media_type']) && $media['media_type'] === 'foto') {
                $file = FCPATH . ltrim($media['media_path'], '/');
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            $projectId = $media['project_id'];
            $mediaModel->delete($mediaId);
            return redirect()->to('/staff/projects/' . $projectId . '/edit')->with('success', 'Media dihapus.');
        }

        return redirect()->back()->with('error', 'Media tidak ditemukan.');
    }

    private function toEmbedUrl(string $url): string
    {
        $trimmed = trim($url);
        if ($trimmed === '') {
            return $url;
        }

        $host = parse_url($trimmed, PHP_URL_HOST);
        if (!$host) {
            return $trimmed;
        }

        if (str_contains($host, 'youtu.be')) {
            $path = ltrim((string) parse_url($trimmed, PHP_URL_PATH), '/');
            return $path ? 'https://www.youtube.com/embed/' . $path : $trimmed;
        }

        if (str_contains($host, 'youtube.com')) {
            parse_str((string) parse_url($trimmed, PHP_URL_QUERY), $query);
            if (!empty($query['v'])) {
                return 'https://www.youtube.com/embed/' . $query['v'];
            }
            $path = (string) parse_url($trimmed, PHP_URL_PATH);
            if (str_starts_with($path, '/shorts/')) {
                return 'https://www.youtube.com/embed/' . ltrim(substr($path, 7), '/');
            }
            if (str_starts_with($path, '/embed/')) {
                return $trimmed;
            }
        }

        return $trimmed;
    }

    // Perangkat Desa Methods
    public function perangkatDesa()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        return view('Staff/perangkat_desa/index');
    }

    public function perangkatDesaApi()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $model = new PerangkatDesaModel();
        
        $page = (int)($this->request->getGet('page') ?? 1);
        $limit = (int)($this->request->getGet('limit') ?? 12);
        $search = $this->request->getGet('search') ?? '';
        $offset = ($page - 1) * $limit;

        // Apply search filter
        if (!empty($search)) {
            $model->groupStart()
                ->like('nama', $search)
                ->orLike('jabatan', $search)
                ->orLike('kontak', $search)
                ->groupEnd();
        }

        // Get total count with search
        $total = $model->countAllResults(false);
        
        // Reset and apply search again for data fetch
        $model = new PerangkatDesaModel();
        if (!empty($search)) {
            $model->groupStart()
                ->like('nama', $search)
                ->orLike('jabatan', $search)
                ->orLike('kontak', $search)
                ->groupEnd();
        }

        $data = $model->orderBy('id', 'DESC')
            ->findAll($limit, $offset);

        // Format data
        foreach ($data as &$item) {
            $item['foto_url'] = $item['foto'] ? base_url($item['foto']) : base_url('assets/img/guest.webp');
        }

        $totalPages = ceil($total / $limit);

        return $this->response->setJSON([
            'data' => $data,
            'total' => $total,
            'total_pages' => $totalPages,
            'current_page' => $page,
        ]);
    }

    public function createPerangkatDesa()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        return view('Staff/perangkat_desa/create');
    }

    public function editPerangkatDesa($id)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $model = new PerangkatDesaModel();
        $item = $model->find($id);

        if (!$item) {
            return redirect()->to('/staff/perangkat-desa')->with('error', 'Data tidak ditemukan.');
        }

        return view('Staff/perangkat_desa/edit', [
            'item' => $item,
        ]);
    }

    public function storePerangkatDesa()
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $model = new PerangkatDesaModel();
        
        $data = [
            'nama' => $this->request->getPost('nama'),
            'jabatan' => $this->request->getPost('jabatan'),
            'kontak' => $this->request->getPost('kontak'),
        ];

        $foto = $this->request->getFile('foto');
        if ($foto && $foto->isValid()) {
            $path = FCPATH . 'uploads/perangkat_desa';
            $this->ensureUploadPath($path);
            $name = $foto->getRandomName();
            $foto->move($path, $name);
            $data['foto'] = 'uploads/perangkat_desa/' . $name;
        }

        $model->insert($data);

        return redirect()->to('/staff/perangkat-desa')->with('success', 'Perangkat desa berhasil ditambahkan.');
    }

    public function updatePerangkatDesa($id)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $model = new PerangkatDesaModel();
        $item = $model->find($id);

        if (!$item) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $data = [
            'nama' => $this->request->getPost('nama'),
            'jabatan' => $this->request->getPost('jabatan'),
            'kontak' => $this->request->getPost('kontak'),
        ];

        $foto = $this->request->getFile('foto');
        if ($foto && $foto->isValid()) {
            // Hapus foto lama jika ada
            if ($item['foto']) {
                $oldFile = FCPATH . ltrim($item['foto'], '/');
                if (is_file($oldFile)) {
                    @unlink($oldFile);
                }
            }

            $path = FCPATH . 'uploads/perangkat_desa';
            $this->ensureUploadPath($path);
            $name = $foto->getRandomName();
            $foto->move($path, $name);
            $data['foto'] = 'uploads/perangkat_desa/' . $name;
        }

        $model->update($id, $data);

        return redirect()->back()->with('success', 'Perangkat desa berhasil diperbarui.');
    }

    public function deletePerangkatDesa($id)
    {
        if ($redirect = $this->guard(['staf'])) {
            return $redirect;
        }

        $model = new PerangkatDesaModel();
        $item = $model->find($id);

        if ($item) {
            // Hapus foto jika ada
            if ($item['foto']) {
                $file = FCPATH . ltrim($item['foto'], '/');
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            $model->delete($id);
            return redirect()->back()->with('success', 'Perangkat desa berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Data tidak ditemukan.');
    }
}


