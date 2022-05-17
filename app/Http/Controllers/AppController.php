<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use DB;

class AppController extends Controller
{
    public function getMenu(Request $request) {
        $email = $request['user']->email;
        $user = User::where('email', $email)->first();
        $userId = $user['id'];
        $pdo = DB::connection('mysql_authen')->getPdo();
        $sql = "select distinct m.menuId as id, m.parentId, m.`Order` as level, m.MenuName as name, b.uri 
                from menus m
                join (select a.MenuId as lv3, b.MenuId as lv2, c.MenuId as lv1, d.MenuId as lv0
                from menus a 
                    left join menus b on a.ParentId=b.MenuId 
                    left join menus c on b.ParentId=c.MenuId 
                    left join menus d on c.ParentId=d.MenuId
                where a.UriId in (
                    select g.UriId uriID
                    from `groups` g
                    where g.GroupId in (
                        select GroupId
                        from userright u
                        where u.UserId = :userId1 and GroupId > 0
                        )
                    UNION
                    select u.UriId
                    from userright u
                    where u.UserId = :userId2
                    and u.UriId > 0)
                    ) a on m.MenuId = a.lv3 or m.MenuId = a.lv2 or m.MenuId = a.lv1 or m.MenuId = a.lv0
                left join `uri` b on m.UriId = b.UriId;";
        // $sql = "select a.id, a.parentId, a.level, a.name, a.uri
        //         from menu a
        //         where a.groupId is null or a.groupId in (
        //             select b.id
        //             from group_report b, user_report_right c
        //             where c.userId = :userId and c.groupId = b.id
        //         );";
        $param = [':userId1' => $userId, ':userId2' => $userId];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($param);
        $menu = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return response()->json([
            'status' => true,
            'menu' => $menu,
        ]);
    }
}
