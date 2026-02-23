<?php

namespace App\Controllers\Guest;

use App\Controllers\BaseController;
use App\Models\DesaProfileModel;
use App\Models\GalleryAlbumModel;
use App\Models\GalleryMediaModel;
use App\Models\NewsModel;
use App\Models\PerangkatDesaModel;
use App\Models\ProjectModel;
use App\Models\ProjectMediaModel;
use App\Models\NewsMediaModel;

class LandingController extends BaseController
{
    public function index()
    {
        $profileModel = new DesaProfileModel();
        $albumModel   = new GalleryAlbumModel();
        $mediaModel   = new GalleryMediaModel();
        $newsModel    = new NewsModel();
        $projectModel = new ProjectModel();

        $profile = $profileModel->first();
        if ($profile && !empty($profile['maps_url'])) {
            $profile['maps_embed_url'] = $this->convertToMapsEmbed($profile['maps_url']);
        }

        // Get albums and their thumbnails
        $albums = $albumModel->orderBy('tanggal_waktu', 'DESC')->findAll(6);
        
        // Get first image media for each album (exclude video links)
        foreach ($albums as &$album) {
            $firstMedia = $mediaModel
                ->where('album_id', $album['id'])
                ->where('media_type', 'image')
                ->first();
            
            // Use media path if available, otherwise use album thumbnail
            $album['thumbnail'] = $firstMedia ? $firstMedia['media_path'] : $album['thumbnail'];
        }

        $data = [
            'desaProfile' => $profile,
            'albums'      => $albums,
            'news'        => $newsModel->orderBy('tanggal_waktu', 'DESC')->findAll(6),
            'projects'    => $projectModel->orderBy('tanggal_waktu', 'DESC')->findAll(6),
        ];

        return view('Guest/home', $data);
    }

    public function profil()
    {
        $profileModel = new DesaProfileModel();
        $perangkatDesaModel = new PerangkatDesaModel();
        
        $profile = $profileModel->first();
        
        if ($profile && !empty($profile['maps_url'])) {
            $profile['maps_embed_url'] = $this->convertToMapsEmbed($profile['maps_url']);
        }

        $perangkatDesa = $perangkatDesaModel->orderBy('id', 'ASC')->findAll();
        // Format foto URL
        foreach ($perangkatDesa as &$perangkat) {
            $perangkat['foto_url'] = $perangkat['foto'] 
                ? base_url($perangkat['foto']) 
                : base_url('assets/img/guest.webp');
        }

        return view('Guest/profil', [
            'desaProfile' => $profile,
            'perangkatDesa' => $perangkatDesa,
        ]);
    }

    public function galeri()
    {
        return view('Guest/galeri');
    }

    public function galeriAjax()
    {
        $albumModel   = new GalleryAlbumModel();
        $mediaModel   = new GalleryMediaModel();
        
        $page = (int) ($this->request->getGet('page') ?? 1);
        $search = $this->request->getGet('search');
        $perPage = 24;
        $offset = ($page - 1) * $perPage;
        
        // Base query
        $query = $albumModel->orderBy('tanggal_waktu', 'DESC');

        // Apply search if exists
        if (!empty($search)) {
            $query->groupStart()
                ->like('nama_album', $search)
                ->orLike('deskripsi', $search)
                ->groupEnd();
        }
        
        // Get total count (with filters)
        $total = $query->countAllResults(false);
        $totalPages = (int) ceil($total / $perPage);
        
        // Get albums for current page
        $albums = $query->findAll($perPage, $offset);
        
        // Escape properties to prevent XSS
        foreach ($albums as &$item) {
            $item['nama_album'] = esc($item['nama_album']);
            $item['deskripsi'] = esc($item['deskripsi']);
        }
        
        $albumMedia = [];
        foreach ($albums as $album) {
            // Get first image media only (exclude video links)
            $firstMedia = $mediaModel
                ->where('album_id', $album['id'])
                ->where('media_type', 'image')
                ->first();
            $albumMedia[$album['id']] = $firstMedia ? $firstMedia['media_path'] : $album['thumbnail'];
        }

        return $this->response->setJSON([
            'success' => true,
            'albums' => $albums,
            'albumMedia' => $albumMedia,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'total' => $total,
                'perPage' => $perPage
            ]
        ]);
    }

    public function galeriDetail($id)
    {
        $albumModel = new GalleryAlbumModel();

        $album = $albumModel->find($id);
        if (!$album) {
            return redirect()->to('/galeri')->with('error', 'Album tidak ditemukan.');
        }

        return view('Guest/galeri_detail', [
            'album'  => $album,
        ]);
    }

    public function galeriDetailAjax($id)
    {
        $mediaModel = new GalleryMediaModel();
        
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 12; // Reverted to 12 items per page
        $offset = ($page - 1) * $perPage;
        
        // Get total count for this album
        $total = $mediaModel->where('album_id', $id)->countAllResults(false);
        $totalPages = (int) ceil($total / $perPage);
        
        // Get media for current page
        $media = $mediaModel
            ->where('album_id', $id)
            ->findAll($perPage, $offset);
        
        // Process video links
        foreach ($media as &$item) {
            if (isset($item['media_type']) && $item['media_type'] === 'video_link') {
                $item['embed_url'] = $this->toEmbedUrl($item['media_path']);
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'media' => $media,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'total' => $total,
                'perPage' => $perPage
            ]
        ]);
    }

    public function berita()
    {
        return view('Guest/berita');
    }

    public function beritaAjax()
    {
        $newsModel = new NewsModel();
        
        $page = (int) ($this->request->getGet('page') ?? 1);
        $search = $this->request->getGet('search');
        $perPage = 24;
        $offset = ($page - 1) * $perPage;
        
        // Base query
        $query = $newsModel->orderBy('tanggal_waktu', 'DESC');

        // Apply search if exists
        if (!empty($search)) {
            $query->groupStart()
                ->like('judul', $search)
                ->orLike('isi', $search)
                ->groupEnd();
        }
        
        // Get total count (with filters)
        $total = $query->countAllResults(false);
        $totalPages = (int) ceil($total / $perPage);
        
        // Get news for current page
        $news = $query->findAll($perPage, $offset);
        
        // Escape properties to prevent XSS
        foreach ($news as &$item) {
            $item['judul'] = esc($item['judul']);
            $item['isi'] = esc($item['isi']);
        }

        return $this->response->setJSON([
            'success' => true,
            'news' => $news,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'total' => $total,
                'perPage' => $perPage
            ]
        ]);
    }

    public function detailBerita($id)
    {
        $newsModel = new NewsModel();
        $news = $newsModel->find($id);

        if (!$news) {
            return redirect()->to('/berita')->with('error', 'Berita tidak ditemukan.');
        }

        return view('Guest/berita_detail', [
            'item' => $news,
        ]);
    }

    public function detailBeritaAjax($id)
    {
        $mediaModel = new NewsMediaModel();
        
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 12;
        $offset = ($page - 1) * $perPage;
        
        // Get total count for this news
        $total = $mediaModel->where('news_id', $id)->countAllResults(false);
        $totalPages = (int) ceil($total / $perPage);
        
        // Get media for current page
        $media = $mediaModel
            ->where('news_id', $id)
            ->findAll($perPage, $offset);
        
        // Process video links
        foreach ($media as &$item) {
            if (isset($item['media_type']) && $item['media_type'] === 'video_link') {
                $item['embed_url'] = $this->toEmbedUrl($item['media_path']);
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'media' => $media,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'total' => $total,
                'perPage' => $perPage
            ]
        ]);
    }

    public function project()
    {
        return view('Guest/project');
    }

    public function projectAjax()
    {
        $projectModel = new ProjectModel();
        
        $page = (int) ($this->request->getGet('page') ?? 1);
        $search = $this->request->getGet('search');
        $perPage = 24;
        $offset = ($page - 1) * $perPage;
        
        // Base query
        $query = $projectModel->orderBy('tanggal_waktu', 'DESC');

        // Apply search if exists
        if (!empty($search)) {
            $query->groupStart()
                ->like('judul', $search)
                ->orLike('deskripsi', $search)
                ->groupEnd();
        }
        
        // Get total count (with filters)
        $total = $query->countAllResults(false);
        $totalPages = (int) ceil($total / $perPage);
        
        // Get projects for current page
        $projects = $query->findAll($perPage, $offset);

        // Escape properties to prevent XSS
        foreach ($projects as &$item) {
            $item['judul'] = esc($item['judul']);
            $item['deskripsi'] = esc($item['deskripsi']);
            $item['status'] = esc($item['status']);
        }

        return $this->response->setJSON([
            'success' => true,
            'projects' => $projects,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'total' => $total,
                'perPage' => $perPage
            ]
        ]);
    }

    public function detailProject($id)
    {
        $projectModel = new ProjectModel();
        $mediaModel   = new ProjectMediaModel();
        $project      = $projectModel->find($id);

        if (!$project) {
            return redirect()->to('/project')->with('error', 'Project tidak ditemukan.');
        }

        $media = $mediaModel->where('project_id', $id)->findAll();
        foreach ($media as &$item) {
            if (isset($item['media_type']) && $item['media_type'] === 'video_link') {
                $item['embed_url'] = $this->toEmbedUrl($item['media_path']);
            }
        }

        return view('Guest/project_detail', [
            'item'  => $project,
            'media' => $media,
        ]);
    }

    public function detailProjectAjax($id)
    {
        $mediaModel = new ProjectMediaModel();
        
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 12;
        $offset = ($page - 1) * $perPage;
        
        // Get total count for this project
        $total = $mediaModel->where('project_id', $id)->countAllResults(false);
        $totalPages = (int) ceil($total / $perPage);
        
        // Get media for current page
        $media = $mediaModel
            ->where('project_id', $id)
            ->findAll($perPage, $offset);
        
        // Process video links
        foreach ($media as &$item) {
            if (isset($item['media_type']) && $item['media_type'] === 'video_link') {
                $item['embed_url'] = $this->toEmbedUrl($item['media_path']);
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'media' => $media,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'total' => $total,
                'perPage' => $perPage
            ]
        ]);
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

        // youtu.be/<id>
        if (str_contains($host, 'youtu.be')) {
            $path = ltrim((string) parse_url($trimmed, PHP_URL_PATH), '/');
            return $path ? 'https://www.youtube.com/embed/' . $path : $trimmed;
        }

        // youtube.com/watch?v=ID or /shorts/ID
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

    private function convertToMapsEmbed(string $url): ?string
    {
        $trimmed = trim($url);
        if ($trimmed === '') {
            return null;
        }

        // Extract URL from iframe tag if user pasted full HTML
        if (preg_match('/src=["\']([^"\']+)["\']/', $trimmed, $matches)) {
            $trimmed = $matches[1];
        }
        // Clean any remaining HTML tags
        $trimmed = strip_tags($trimmed);
        $trimmed = trim($trimmed);

        // Jika sudah embed URL, langsung return
        if (str_contains($trimmed, '/maps/embed') || str_contains($trimmed, 'maps/embed')) {
            return $trimmed;
        }

        // Handle Google Maps regular URL: https://www.google.com/maps?q=... atau /maps/place/...
        if (str_contains($trimmed, 'google.com/maps')) {
            // Coba extract place ID atau query
            $parsed = parse_url($trimmed);
            $host = $parsed['host'] ?? '';
            
            if (str_contains($host, 'maps.google.com') || str_contains($host, 'google.com')) {
                // Jika ada query parameter q, gunakan itu
                if (isset($parsed['query'])) {
                    parse_str($parsed['query'], $query);
                    if (!empty($query['q'])) {
                        return 'https://www.google.com/maps?q=' . urlencode($query['q']) . '&output=embed';
                    }
                }
                
                // Jika ada path seperti /place/..., coba extract
                if (isset($parsed['path'])) {
                    if (preg_match('#/place/([^/]+)#', $parsed['path'], $matches)) {
                        $place = urlencode($matches[1]);
                        return 'https://www.google.com/maps?q=' . $place . '&output=embed';
                    }
                }
            }
        }

        // Untuk Google Share link atau link lainnya yang tidak bisa dikonversi
        // Return null agar view bisa handle dengan menampilkan link biasa
        return null;
    }
}


