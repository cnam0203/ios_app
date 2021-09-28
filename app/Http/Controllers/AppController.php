<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class AppController extends Controller
{
    public function getMenu($userId) {
        $pdo = DB::connection('mysql_authen')->getPdo();
        $sql = "select a.id, a.parentId, a.level, a.name, a.uri
                from menu a
                where a.groupId is null or a.groupId in (
                    select b.id
                    from group_report b, user_report_right c
                    where c.userId = :userId and c.groupId = b.id
                );";
        $param = [':userId' => $userId];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($param);
        $menu = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $menu;
    }
}
