<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Helpers yang otomatis dimuat di seluruh controller.
     *
     * @var array
     */
    protected $helpers = ['url', 'form', 'text', 'htmlpurifier'];

    /**
     * Session service instance.
     *
     * @var \CodeIgniter\Session\Session
     */
    protected $session;

    /**
     * Data user yang sedang login (jika ada).
     *
     * @var array|null
     */
    protected $currentUser;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->session = service('session');
        $this->currentUser = $this->session->get('user');
    }
}
