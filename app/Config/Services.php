<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use CodeIgniter\Shield\Auth;

class Services extends BaseService
{
    /**
     * Shield の Authorization サービスをオーバーライド
     */
    public static function authorization(bool $getShared = true)
    {
        if ($getShared) {
            return self::getSharedInstance('authorization');
        }

        // Shield の Auth を取得し、Authorization を返す
        return auth()->getAuthorization();
    }
}
