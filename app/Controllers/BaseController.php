<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = service('session');
    }

        /**
     * **汎用ログ出力関数**
     *
     * @param string $entityType エンティティの種類（reservation, resource, account, user など）
     * @param string $action 操作内容（created, updated, deleted, failed）
     * @param int|null $entityId 操作対象のID
     * @param array $data 操作対象のデータ
     * @param int|null $actorId 操作を実行したユーザーID（null の場合はログインユーザー）
     * @param string|null $errorMessage エラー発生時のメッセージ（エラー時のみ）
     */
    protected function logAction(string $entityType, string $action, ?int $entityId, array $data, ?int $actorId = null, ?string $errorMessage = null)
    {
        $actorId = $actorId ?? auth()->user()->id;
        $actorName = auth()->user()->fullname ?? "Unknown User";

        $logData = [
            'entity_type' => $entityType, // reservation, resource, account, user など
            'entity_id' => $entityId, // 変更対象のID
            'actor_id' => $actorId,  // 操作を実行したユーザー
            'actor_name' => $actorName,
            'action' => $action, // created, updated, deleted, failed
            'data' => $data, // 操作内容
        ];

        if ($errorMessage) {
            $logData['error'] = $errorMessage;
            log_message('error', json_encode($logData, JSON_UNESCAPED_UNICODE));
        } else {
            log_message('info', json_encode($logData, JSON_UNESCAPED_UNICODE));
        }
    }
}
