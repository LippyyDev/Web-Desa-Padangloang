<?php

namespace App\Controllers\Staff;

use App\Controllers\ProtectedController;
use App\Models\DesaProfileModel;
use App\Models\GalleryAlbumModel;
use App\Models\GalleryMediaModel;
use App\Models\NewsMediaModel;
use App\Models\NewsModel;
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
            'maps_url'            => $this->request->getPost('maps_url'),
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

        $albumModel = new GalleryAlbumModel();

        return view('Staff/gallery/index', [
            'albums' => $albumModel->orderBy('tanggal_waktu', 'DESC')->findAll(),
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
            $path = FCPATH . 'uploads/gallery';
            $this->ensureUploadPath($path);
            $name = $thumb->getRandomName();
            $thumb->move($path, $name);
            $data['thumbnail'] = 'uploads/gallery/' . $name;
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

        foreach ($files as $file) {
            if (!$file->isValid()) {
                continue;
            }
            $name = $file->getRandomName();
            $file->move($path, $name);
            $mediaModel->insert([
                'album_id'   => $albumId,
                'media_type' => 'foto',
                'media_path' => 'uploads/gallery/' . $name,
            ]);
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

        $newsModel = new NewsModel();

        return view('Staff/news/index', [
            'news' => $newsModel->orderBy('tanggal_waktu', 'DESC')->findAll(),
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
                $file = FCPATH . ltrim($media['media_path'], '/');
                if (is_file($file)) {
                    @unlink($file);
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

        $projectModel = new ProjectModel();

        return view('Staff/projects/index', [
            'projects' => $projectModel->orderBy('tanggal_waktu', 'DESC')->findAll(),
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
            $m['embed_url'] = $this->toEmbedUrl($m['media_path']);
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
                $file = FCPATH . ltrim($media['media_path'], '/');
                if (is_file($file)) {
                    @unlink($file);
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
}


