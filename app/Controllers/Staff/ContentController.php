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

        // M1: Validasi input profil desa
        $validationRules = [
            'visi'             => 'permit_empty|max_length[2000]',
            'misi'             => 'permit_empty|max_length[5000]',
            'jumlah_penduduk'  => 'permit_empty|integer',
            'jumlah_kk'        => 'permit_empty|integer',
            'penduduk_sementara' => 'permit_empty|integer',
            'jumlah_laki'      => 'permit_empty|integer',
            'jumlah_perempuan' => 'permit_empty|integer',
            'mutasi_penduduk'  => 'permit_empty|integer',
            'kontak_wa'        => 'permit_empty|max_length[20]|regex_match[/^[\d\s\+\-\(\)]+$/]',
            'kontak_email'     => 'permit_empty|valid_email|max_length[100]',
            'alamat_kantor'    => 'permit_empty|max_length[500]',
            'deskripsi_lokasi' => 'permit_empty|max_length[1000]',
        ];

        if (!$this->validate($validationRules)) {
            $errors = implode(' ', $this->validator->getErrors());
            return redirect()->back()->withInput()->with('error', 'Validasi gagal: ' . $errors);
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
            'jumlah_penduduk'     => $this->request->getPost('jumlah_penduduk') ?: null,
            'jumlah_kk'           => $this->request->getPost('jumlah_kk') ?: null,
            'penduduk_sementara'  => $this->request->getPost('penduduk_sementara') ?: null,
            'jumlah_laki'         => $this->request->getPost('jumlah_laki') ?: null,
            'jumlah_perempuan'    => $this->request->getPost('jumlah_perempuan') ?: null,
            'mutasi_penduduk'     => $this->request->getPost('mutasi_penduduk') ?: null,
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
        $csrfToken  = csrf_token();

        $page      = (int)($this->request->getPost('page') ?? 1);
        $limit     = (int)($this->request->getPost('limit') ?? 12);
        $search    = $this->request->getPost('search') ?? '';
        $dateStart = $this->request->getPost('date_start') ?? '';
        $dateEnd   = $this->request->getPost('date_end') ?? '';
        $offset    = ($page - 1) * $limit;

        // Apply date filter
        if (!empty($dateStart)) {
            $albumModel->where('DATE(tanggal_waktu) >=', $dateStart);
        }
        if (!empty($dateEnd)) {
            $albumModel->where('DATE(tanggal_waktu) <=', $dateEnd);
        }

        // Apply search filter
        if (!empty($search)) {
            $albumModel->groupStart()
                ->like('nama_album', $search)
                ->orLike('deskripsi', $search)
                ->groupEnd();
        }

        // Get total count with filters
        $total = $albumModel->countAllResults(false);

        // Reset and apply filters again for data fetch
        $albumModel = new GalleryAlbumModel();

        // Apply date filter again
        if (!empty($dateStart)) {
            $albumModel->where('DATE(tanggal_waktu) >=', $dateStart);
        }
        if (!empty($dateEnd)) {
            $albumModel->where('DATE(tanggal_waktu) <=', $dateEnd);
        }

        // Apply search filter again
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
                'id'            => $album['id'],
                'nama_album'    => esc($album['nama_album']),
                'deskripsi'     => esc($album['deskripsi'] ?? ''),
                'tanggal_waktu' => date('d M Y', strtotime($album['tanggal_waktu'])),
                'thumbnail'     => $album['thumbnail'] ? base_url($album['thumbnail']) : '',
            ];
        }

        return $this->response->setJSON([
            $csrfToken    => csrf_hash(),
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'limit'       => $limit,
            'total_pages' => ceil($total / $limit),
            'search'      => $search,
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
            $imgError = $this->validateImageFile($thumb);
            if ($imgError !== null) {
                return redirect()->back()->with('error', $imgError);
            }
            $ext = strtolower(pathinfo($thumb->getClientName(), PATHINFO_EXTENSION));
            $path = FCPATH . 'uploads/gallery';
            $this->ensureUploadPath($path);
            $tempName = $thumb->getRandomName();
            $thumb->move($path, $tempName);
            $tempPath = $path . '/' . $tempName;
            $this->fixImageOrientation($tempPath);
            $webpName = pathinfo($tempName, PATHINFO_FILENAME) . '.webp';
            if (!$this->convertToWebp($tempPath, $path . '/' . $webpName, $ext)) {
                return redirect()->back()->with('error', 'Gagal memproses gambar thumbnail.');
            }
            $data['thumbnail'] = 'uploads/gallery/' . $webpName;
        }

        $albumId = $albumModel->insert($data, true);
        $this->saveGalleryMedia($albumId);

        return redirect()->to('/staff/galeri')->with('success', 'Album galeri disimpan.');
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
            $imgError = $this->validateImageFile($thumb);
            if ($imgError !== null) {
                return redirect()->back()->with('error', $imgError);
            }
            $ext = strtolower(pathinfo($thumb->getClientName(), PATHINFO_EXTENSION));
            $path = FCPATH . 'uploads/gallery';
            $this->ensureUploadPath($path);
            // Hapus thumbnail lama jika ada
            if ($album['thumbnail']) {
                $oldFile = FCPATH . ltrim($album['thumbnail'], '/');
                if (is_file($oldFile)) {
                    @unlink($oldFile);
                }
            }
            $tempName = $thumb->getRandomName();
            $thumb->move($path, $tempName);
            $tempPath = $path . '/' . $tempName;
            $this->fixImageOrientation($tempPath);
            $webpName = pathinfo($tempName, PATHINFO_FILENAME) . '.webp';
            if (!$this->convertToWebp($tempPath, $path . '/' . $webpName, $ext)) {
                return redirect()->back()->with('error', 'Gagal memproses gambar thumbnail.');
            }
            $data['thumbnail'] = 'uploads/gallery/' . $webpName;
        }

        $albumModel->update($id, $data);
        $this->saveGalleryMedia($id);

        return redirect()->to('/staff/galeri')->with('success', 'Album diperbarui.');
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

        return redirect()->to('/staff/galeri')->with('success', 'Album dihapus.');
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
            // Validasi berlapis: ukuran, nama file, MIME, magic bytes, content scan
            if ($this->validateImageFile($file) !== null) {
                continue;
            }
            $extension = strtolower(pathinfo($file->getClientName(), PATHINFO_EXTENSION));
            $tempName = $file->getRandomName();
            $file->move($path, $tempName);
            $tempPath = $path . '/' . $tempName;
            $this->fixImageOrientation($tempPath);
            $webpName = pathinfo($tempName, PATHINFO_FILENAME) . '.webp';
            $webpPath = $path . '/' . $webpName;
            if ($this->convertToWebp($tempPath, $webpPath, $extension)) {
                $mediaModel->insert([
                    'album_id'   => $albumId,
                    'media_type' => 'foto',
                    'media_path' => 'uploads/gallery/' . $webpName,
                ]);
            }
        }

        $videoLinks = $this->request->getPost('video_links');
        if (is_array($videoLinks)) {
            foreach ($videoLinks as $block) {
                $lines = preg_split('/\r\n|\r|\n/', (string) $block);
                foreach ($lines as $link) {
                    $link = trim($link);
                    if ($link === '') continue;

                    // M3: Hanya izinkan URL dari domain YouTube
                    $host = @parse_url($link, PHP_URL_HOST);
                    $allowedHosts = ['youtube.com', 'www.youtube.com', 'youtu.be', 'm.youtube.com'];
                    if (!$host || !in_array(strtolower($host), $allowedHosts)) {
                        continue; // Skip URL dari domain yang tidak diizinkan
                    }

                    $mediaModel->insert([
                        'album_id'   => $albumId,
                        'media_type' => 'video_link',
                        'media_path' => $link,
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
        $csrfToken = csrf_token();

        $page      = (int)($this->request->getPost('page') ?? 1);
        $limit     = (int)($this->request->getPost('limit') ?? 12);
        $search    = $this->request->getPost('search') ?? '';
        $dateStart = $this->request->getPost('date_start') ?? '';
        $dateEnd   = $this->request->getPost('date_end') ?? '';
        $offset    = ($page - 1) * $limit;

        // Apply date filter
        if (!empty($dateStart)) {
            $newsModel->where('DATE(tanggal_waktu) >=', $dateStart);
        }
        if (!empty($dateEnd)) {
            $newsModel->where('DATE(tanggal_waktu) <=', $dateEnd);
        }

        // Apply search filter
        if (!empty($search)) {
            $newsModel->groupStart()
                ->like('judul', $search)
                ->orLike('isi', $search)
                ->groupEnd();
        }

        // Get total count with filters
        $total = $newsModel->countAllResults(false);

        // Reset and apply filters again for data fetch
        $newsModel = new NewsModel();

        // Apply date filter again
        if (!empty($dateStart)) {
            $newsModel->where('DATE(tanggal_waktu) >=', $dateStart);
        }
        if (!empty($dateEnd)) {
            $newsModel->where('DATE(tanggal_waktu) <=', $dateEnd);
        }

        // Apply search filter again
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
                'id'            => $item['id'],
                'judul'         => esc($item['judul']),
                'isi'           => strip_tags($item['isi']),
                'tanggal_waktu' => date('d M Y', strtotime($item['tanggal_waktu'])),
                'thumbnail'     => $item['thumbnail'] ? base_url($item['thumbnail']) : '',
            ];
        }

        return $this->response->setJSON([
            $csrfToken    => csrf_hash(),
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'limit'       => $limit,
            'total_pages' => ceil($total / $limit),
            'search'      => $search,
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
            $imgError = $this->validateImageFile($thumb);
            if ($imgError !== null) {
                return redirect()->back()->with('error', $imgError);
            }
            $ext = strtolower(pathinfo($thumb->getClientName(), PATHINFO_EXTENSION));
            $path = FCPATH . 'uploads/news';
            $this->ensureUploadPath($path);
            $tempName = $thumb->getRandomName();
            $thumb->move($path, $tempName);
            $tempPath = $path . '/' . $tempName;
            $this->fixImageOrientation($tempPath);
            $webpName = pathinfo($tempName, PATHINFO_FILENAME) . '.webp';
            if (!$this->convertToWebp($tempPath, $path . '/' . $webpName, $ext)) {
                return redirect()->back()->with('error', 'Gagal memproses gambar thumbnail.');
            }
            $data['thumbnail'] = 'uploads/news/' . $webpName;
        }

        $newsId = $newsModel->insert($data, true);
        $this->saveNewsMedia($newsId);

        return redirect()->to('/staff/berita')->with('success', 'Berita disimpan.');
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
            $imgError = $this->validateImageFile($thumb);
            if ($imgError !== null) {
                return redirect()->back()->with('error', $imgError);
            }
            $ext = strtolower(pathinfo($thumb->getClientName(), PATHINFO_EXTENSION));
            $path = FCPATH . 'uploads/news';
            $this->ensureUploadPath($path);
            $tempName = $thumb->getRandomName();
            $thumb->move($path, $tempName);
            $tempPath = $path . '/' . $tempName;
            $this->fixImageOrientation($tempPath);
            $webpName = pathinfo($tempName, PATHINFO_FILENAME) . '.webp';
            if (!$this->convertToWebp($tempPath, $path . '/' . $webpName, $ext)) {
                return redirect()->back()->with('error', 'Gagal memproses gambar thumbnail.');
            }
            $data['thumbnail'] = 'uploads/news/' . $webpName;
        }

        $newsModel->update($id, $data);
        $this->saveNewsMedia($id);

        return redirect()->to('/staff/berita')->with('success', 'Berita diperbarui.');
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

        return redirect()->to('/staff/berita')->with('success', 'Berita dihapus.');
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
                // Validasi berlapis: ukuran, nama file, MIME, magic bytes, content scan
                if ($this->validateImageFile($file) !== null) {
                    continue;
                }
                $extension = strtolower(pathinfo($file->getClientName(), PATHINFO_EXTENSION));
                $tempName = $file->getRandomName();
                $file->move($path, $tempName);
                $tempPath = $path . '/' . $tempName;
                $this->fixImageOrientation($tempPath);
                $webpName = pathinfo($tempName, PATHINFO_FILENAME) . '.webp';
                $webpPath = $path . '/' . $webpName;
                if ($this->convertToWebp($tempPath, $webpPath, $extension)) {
                    $mediaModel->insert([
                        'news_id'    => $newsId,
                        'media_type' => 'foto',
                        'media_path' => 'uploads/news/' . $webpName,
                    ]);
                }
            }
        }

        $videoLinks = $this->request->getPost('video_links');
        if (is_array($videoLinks)) {
            $mediaModel = new NewsMediaModel();
            foreach ($videoLinks as $block) {
                $lines = preg_split('/\r\n|\r|\n/', (string) $block);
                foreach ($lines as $link) {
                    $link = trim($link);
                    if ($link === '') continue;

                    // M3: Hanya izinkan URL dari domain YouTube
                    $host = @parse_url($link, PHP_URL_HOST);
                    $allowedHosts = ['youtube.com', 'www.youtube.com', 'youtu.be', 'm.youtube.com'];
                    if (!$host || !in_array(strtolower($host), $allowedHosts)) {
                        continue;
                    }

                    $mediaModel->insert([
                        'news_id'    => $newsId,
                        'media_type' => 'video_link',
                        'media_path' => $link,
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
        $csrfToken    = csrf_token();

        $page         = (int)($this->request->getPost('page') ?? 1);
        $limit        = (int)($this->request->getPost('limit') ?? 12);
        $search       = $this->request->getPost('search') ?? '';
        $dateStart    = $this->request->getPost('date_start') ?? '';
        $dateEnd      = $this->request->getPost('date_end') ?? '';
        $statusFilter = $this->request->getPost('status_filter') ?? '';
        $offset       = ($page - 1) * $limit;

        // Apply date filter
        if (!empty($dateStart)) {
            $projectModel->where('DATE(tanggal_waktu) >=', $dateStart);
        }
        if (!empty($dateEnd)) {
            $projectModel->where('DATE(tanggal_waktu) <=', $dateEnd);
        }

        // Apply status filter
        if (!empty($statusFilter)) {
            $projectModel->where('status', $statusFilter);
        }

        // Apply search filter
        if (!empty($search)) {
            $projectModel->groupStart()
                ->like('judul', $search)
                ->orLike('deskripsi', $search)
                ->orLike('status', $search)
                ->groupEnd();
        }

        // Get total count with filters
        $total = $projectModel->countAllResults(false);

        // Reset and apply filters again for data fetch
        $projectModel = new ProjectModel();

        // Apply date filter again
        if (!empty($dateStart)) {
            $projectModel->where('DATE(tanggal_waktu) >=', $dateStart);
        }
        if (!empty($dateEnd)) {
            $projectModel->where('DATE(tanggal_waktu) <=', $dateEnd);
        }

        // Apply status filter again
        if (!empty($statusFilter)) {
            $projectModel->where('status', $statusFilter);
        }

        // Apply search filter again
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
                'id'            => $project['id'],
                'judul'         => esc($project['judul']),
                'deskripsi'     => strip_tags($project['deskripsi'] ?? ''),
                'status'        => esc($project['status']),
                'anggaran'      => $project['anggaran'] ?? 0,
                'tanggal_waktu' => date('d M Y', strtotime($project['tanggal_waktu'])),
                'thumbnail'     => $project['thumbnail'] ? base_url($project['thumbnail']) : '',
            ];
        }

        return $this->response->setJSON([
            $csrfToken    => csrf_hash(),
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'limit'       => $limit,
            'total_pages' => ceil($total / $limit),
            'search'      => $search,
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
            $imgError = $this->validateImageFile($thumb);
            if ($imgError !== null) {
                return redirect()->back()->with('error', $imgError);
            }
            $ext = strtolower(pathinfo($thumb->getClientName(), PATHINFO_EXTENSION));
            $path = FCPATH . 'uploads/projects';
            $this->ensureUploadPath($path);
            $tempName = $thumb->getRandomName();
            $thumb->move($path, $tempName);
            $tempPath = $path . '/' . $tempName;
            $webpName = pathinfo($tempName, PATHINFO_FILENAME) . '.webp';
            if (!$this->convertToWebp($tempPath, $path . '/' . $webpName, $ext)) {
                return redirect()->back()->with('error', 'Gagal memproses gambar thumbnail.');
            }
            $data['thumbnail'] = 'uploads/projects/' . $webpName;
        }

        $projectId = $projectModel->insert($data, true);
        $this->saveProjectMedia($projectId);

        return redirect()->to('/staff/projects')->with('success', 'Project disimpan.');
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
            $imgError = $this->validateImageFile($thumb);
            if ($imgError !== null) {
                return redirect()->back()->with('error', $imgError);
            }
            $ext = strtolower(pathinfo($thumb->getClientName(), PATHINFO_EXTENSION));
            $path = FCPATH . 'uploads/projects';
            $this->ensureUploadPath($path);
            $tempName = $thumb->getRandomName();
            $thumb->move($path, $tempName);
            $tempPath = $path . '/' . $tempName;
            $webpName = pathinfo($tempName, PATHINFO_FILENAME) . '.webp';
            if (!$this->convertToWebp($tempPath, $path . '/' . $webpName, $ext)) {
                return redirect()->back()->with('error', 'Gagal memproses gambar thumbnail.');
            }
            $data['thumbnail'] = 'uploads/projects/' . $webpName;
        }

        $projectModel->update($id, $data);
        $this->saveProjectMedia($id);

        return redirect()->to('/staff/projects')->with('success', 'Project diperbarui.');
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

        return redirect()->to('/staff/projects')->with('success', 'Project dihapus.');
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
                // Validasi berlapis: ukuran, nama file, MIME, magic bytes, content scan
                if ($this->validateImageFile($file) !== null) {
                    continue; // Skip file tidak valid
                }
                $extension = strtolower(pathinfo($file->getClientName(), PATHINFO_EXTENSION));
                $tempName = $file->getRandomName();
                $file->move($path, $tempName);
                $tempPath = $path . '/' . $tempName;
                $webpName = pathinfo($tempName, PATHINFO_FILENAME) . '.webp';
                $webpPath = $path . '/' . $webpName;
                if ($this->convertToWebp($tempPath, $webpPath, $extension)) {
                    $mediaModel->insert([
                        'project_id' => $projectId,
                        'media_type' => 'foto',
                        'media_path' => 'uploads/projects/' . $webpName,
                    ]);
                }
            }
        }

        $videoLinks = $this->request->getPost('video_links');
        if (is_array($videoLinks)) {
            $mediaModel = new ProjectMediaModel();
            foreach ($videoLinks as $block) {
                $lines = preg_split('/\r\n|\r|\n/', (string) $block);
                foreach ($lines as $link) {
                    $link = trim($link);
                    if ($link === '') continue;

                    // M3: Hanya izinkan URL dari domain YouTube
                    $host = @parse_url($link, PHP_URL_HOST);
                    $allowedHosts = ['youtube.com', 'www.youtube.com', 'youtu.be', 'm.youtube.com'];
                    if (!$host || !in_array(strtolower($host), $allowedHosts)) {
                        continue;
                    }

                    $mediaModel->insert([
                        'project_id' => $projectId,
                        'media_type' => 'video_link',
                        'media_path' => $link,
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

        $model     = new PerangkatDesaModel();
        $csrfToken = csrf_token();

        $page   = (int)($this->request->getPost('page') ?? 1);
        $limit  = (int)($this->request->getPost('limit') ?? 12);
        $search = $this->request->getPost('search') ?? '';
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
            $csrfToken     => csrf_hash(),
            'data'         => $data,
            'total'        => $total,
            'total_pages'  => $totalPages,
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
        
        $jabatan = trim($this->request->getPost('jabatan'));
        if (strtolower($jabatan) === 'kepala desa') {
            return redirect()->back()->withInput()->with('error', 'Tidak dapat menambahkan Kepala Desa baru.');
        }

        $data = [
            'nama' => $this->request->getPost('nama'),
            'jabatan' => $jabatan,
            'kontak' => $this->request->getPost('kontak'),
        ];

        
        $foto = $this->request->getFile('foto');
        if ($foto && $foto->isValid()) {
            $imgError = $this->validateImageFile($foto);
            if ($imgError !== null) {
                return redirect()->back()->with('error', $imgError);
            }
            $ext = strtolower(pathinfo($foto->getClientName(), PATHINFO_EXTENSION));
            $path = FCPATH . 'uploads/perangkat_desa';
            $this->ensureUploadPath($path);
            $tempName = $foto->getRandomName();
            $foto->move($path, $tempName);
            $tempPath = $path . '/' . $tempName;
            $this->fixImageOrientation($tempPath);
            $webpName = pathinfo($tempName, PATHINFO_FILENAME) . '.webp';
            if (!$this->convertToWebp($tempPath, $path . '/' . $webpName, $ext)) {
                return redirect()->back()->with('error', 'Gagal memproses gambar. Pastikan file adalah gambar yang valid.');
            }
            $data['foto'] = 'uploads/perangkat_desa/' . $webpName;
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

        $jabatan = trim($this->request->getPost('jabatan'));
        $isKepalaDesa = (strtolower($item['jabatan']) === 'kepala desa');

        if (strtolower($jabatan) === 'kepala desa') {
            $jabatan = 'Kepala Desa';
        }

        if ($isKepalaDesa) {
            $jabatan = 'Kepala Desa';
        } elseif (strtolower($jabatan) === 'kepala desa') {
            return redirect()->back()->withInput()->with('error', 'Tidak dapat mengubah perangkat desa menjadi Kepala Desa.');
        }

        $data = [
            'nama' => $this->request->getPost('nama'),
            'jabatan' => $jabatan,
            'kontak' => $this->request->getPost('kontak'),
        ];

        $foto = $this->request->getFile('foto');
        if ($foto && $foto->isValid()) {
            $imgError = $this->validateImageFile($foto);
            if ($imgError !== null) {
                return redirect()->back()->with('error', $imgError);
            }
            // Hapus foto lama jika ada
            if ($item['foto']) {
                $oldFile = FCPATH . ltrim($item['foto'], '/');
                if (is_file($oldFile)) {
                    @unlink($oldFile);
                }
            }
            $ext = strtolower(pathinfo($foto->getClientName(), PATHINFO_EXTENSION));
            $path = FCPATH . 'uploads/perangkat_desa';
            $this->ensureUploadPath($path);
            $tempName = $foto->getRandomName();
            $foto->move($path, $tempName);
            $tempPath = $path . '/' . $tempName;
            $this->fixImageOrientation($tempPath);
            $webpName = pathinfo($tempName, PATHINFO_FILENAME) . '.webp';
            if (!$this->convertToWebp($tempPath, $path . '/' . $webpName, $ext)) {
                return redirect()->back()->with('error', 'Gagal memproses gambar. Pastikan file adalah gambar yang valid.');
            }
            $data['foto'] = 'uploads/perangkat_desa/' . $webpName;
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
            if (strtolower($item['jabatan']) === 'kepala desa') {
                return redirect()->back()->with('error', 'Data Kepala Desa tidak dapat dihapus.');
            }
            // Hapus foto jika ada
            if ($item['foto']) {
                $file = FCPATH . ltrim($item['foto'], '/');
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            $model->delete($id);
            return redirect()->to('/staff/perangkat-desa')->with('success', 'Perangkat desa berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Data tidak ditemukan.');
    }

    /**
     * Convert image to WebP.
     * If the source is already WEBP, simply renames the temp file (avoids GD bug
     * where imagecreatefrom* cannot open a WEBP source).
     * Returns true on success, false on failure.
     */
    /**
     * Validasi file gambar secara berlapis (8 lapisan).
     * Mengembalikan string pesan error jika gagal, atau null jika lolos.
     *
     * Lapisan:
     *  1. Batas ukuran 5MB
     *  2a. Null byte di nama file
     *  2b. Path traversal di nama file
     *  2c. Karakter berbahaya di nama file
     *  2d. Unicode RTL override spoofing
     *  3.  Ekstensi ganda berbahaya
     *  4.  Whitelist ekstensi akhir (jpg, jpeg, png, webp)
     *  5.  Whitelist Client MIME
     *  6.  Magic bytes validation
     *  7.  Content scan (21 pola berbahaya)
     *  8.  WEBP chunk signature deep-check
     */
    private function validateImageFile(\CodeIgniter\HTTP\Files\UploadedFile $file): ?string
    {
        // 1. Ukuran maks 5MB
        if ($file->getSize() > 5242880) {
            return 'Ukuran file melebihi batas maksimal 5MB.';
        }

        $clientName = $file->getClientName();

        // 2a. Null byte
        if (strpos($clientName, "\0") !== false) {
            return 'Nama file tidak valid.';
        }

        // 2b. Path traversal
        if (basename($clientName) !== $clientName) {
            return 'Nama file tidak valid.';
        }

        // 2c. Karakter berbahaya
        if (preg_match('/[<>:"\/\\\\|?*\x00-\x1F]/', $clientName)) {
            return 'Nama file mengandung karakter yang tidak diperbolehkan.';
        }

        // 2d. Unicode RTL override spoofing
        if (preg_match('/[\x{200F}\x{202E}\x{202B}\x{202D}]/u', $clientName)) {
            return 'Nama file mengandung karakter tidak valid.';
        }

        // 3. Ekstensi ganda berbahaya
        $dangerousExts = [
            'php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar',
            'asp', 'aspx', 'jsp', 'exe', 'sh', 'bat', 'cmd', 'py',
            'rb', 'pl', 'cgi', 'htaccess', 'htpasswd', 'svg', 'shtml', 'pht',
        ];
        $nameParts = explode('.', $clientName);
        if (count($nameParts) > 2) {
            for ($i = 0; $i < count($nameParts) - 1; $i++) {
                if (in_array(strtolower($nameParts[$i]), $dangerousExts)) {
                    return 'Format file tidak diperbolehkan.';
                }
            }
        }

        // 4. Whitelist ekstensi akhir
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($clientName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) {
            return 'Format file tidak diperbolehkan. Hanya JPG, JPEG, PNG, dan WEBP.';
        }

        // 5. Client MIME whitelist
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array(strtolower(trim($file->getClientMimeType())), $allowedMimes)) {
            return 'Tipe MIME file tidak diperbolehkan.';
        }

        // 6. Magic bytes
        $tmpPath = $file->getTempName();
        $fh      = @fopen($tmpPath, 'rb');
        $header  = $fh ? fread($fh, 12) : '';
        if ($fh) fclose($fh);

        $magicMap = [
            'jpg'  => ["\xFF\xD8\xFF"],
            'jpeg' => ["\xFF\xD8\xFF"],
            'png'  => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
            'webp' => ["RIFF"],
        ];
        if (isset($magicMap[$ext])) {
            $magicOk = false;
            foreach ($magicMap[$ext] as $magic) {
                if (str_starts_with($header, $magic)) { $magicOk = true; break; }
            }
            if (!$magicOk) {
                return 'File gambar tidak valid atau telah dimanipulasi.';
            }
        }

        // 7. Content scan — pola skrip berbahaya
        $fileContent = @file_get_contents($tmpPath);
        if ($fileContent === false) {
            return 'Gagal membaca file.';
        }
        $dangerousPatterns = [
            '/\<\?php/i', '/\<\?=/i', '/<script[\s>]/i',
            '/eval\s*\(/i', '/exec\s*\(/i', '/system\s*\(/i',
            '/passthru\s*\(/i', '/shell_exec\s*\(/i', '/base64_decode\s*\(/i',
            '/preg_replace\s*\(.*\/e/i', '/assert\s*\(/i',
            '/create_function\s*\(/i', '/call_user_func(?:_array)?\s*\(/i',
            '/file_put_contents\s*\(/i', '/str_rot13\s*\(/i',
            '/\$_(?:GET|POST|REQUEST|COOKIE|SERVER|FILES|ENV)/i',
            '/phar:\/\//i', '/data:[^,]*base64/i',
            '/javascript:/i', '/vbscript:/i', '/on\w+\s*=/i',
        ];
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $fileContent)) {
                return 'File mengandung konten yang tidak diperbolehkan.';
            }
        }

        // 8. WEBP chunk signature deep-check
        if ($ext === 'webp') {
            if (strlen($fileContent) < 12 || substr($fileContent, 8, 4) !== 'WEBP') {
                return 'File WEBP tidak valid.';
            }
        }

        return null; // Semua validasi lulus
    }

    private function convertToWebp(string $tempPath, string $webpPath, string $extension): bool
    {
        try {
            if (strtolower($extension) === 'webp') {
                // Source is already WEBP — GD cannot re-open it via convert().
                // Simply rename/copy to the destination path.
                if (!rename($tempPath, $webpPath)) {
                    copy($tempPath, $webpPath);
                    @unlink($tempPath);
                }
            } else {
                $image = \Config\Services::image();
                $image->withFile($tempPath)
                    ->convert(IMAGETYPE_WEBP)
                    ->save($webpPath, 85);
                if (file_exists($tempPath)) {
                    @unlink($tempPath);
                }
            }
            return true;
        } catch (\Exception $e) {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
            log_message('error', 'convertToWebp failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fix image orientation based on EXIF data
     * This prevents images from being rotated incorrectly when uploaded from smartphones
     */
    private function fixImageOrientation(string $filePath): void
    {
        if (!function_exists('exif_read_data')) {
            return; // EXIF extension not available
        }

        $exif = @exif_read_data($filePath);
        if (!$exif || !isset($exif['Orientation'])) {
            return; // No EXIF orientation data
        }

        $image = \Config\Services::image();
        $orientation = $exif['Orientation'];

        try {
            switch ($orientation) {
                case 3:
                    $image->withFile($filePath)
                        ->rotate(180)
                        ->save($filePath);
                    break;
                case 6:
                    $image->withFile($filePath)
                        ->rotate(270)
                        ->save($filePath);
                    break;
                case 8:
                    $image->withFile($filePath)
                        ->rotate(90)
                        ->save($filePath);
                    break;
            }
        } catch (\Exception $e) {
            // Silently fail if rotation fails
            log_message('error', 'Failed to fix image orientation: ' . $e->getMessage());
        }
    }
}


