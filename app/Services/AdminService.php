<?php

namespace App\Services;

use App\Models\Admin;
use Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

class AdminService implements IAdminService
{
    public function create($attributes = [])
    {
        try {
           // dd($attributes);
            return Admin::create($attributes);
        } catch (Throwable $e) {
            throw new BadRequestHttpException($e->getMessage(), null, 400);
        }
    }

    public function getList($num=10,$page=1,$searchKeyword='',$id)
    {
        try {
        // Xây dựng query Eloquent
        $query = Admin::query();
        if($id){
            $query->where('id','=',$id);
        }
        elseif ($searchKeyword!=='') {
            $query->where('username', 'like', '%' . $searchKeyword . '%')
                  ->orWhere('email', 'like', '%' . $searchKeyword . '%');
        }

        // Lấy tổng số admin
        $totalAdmins = $query->count();

        // Thực hiện phân trang và lấy dữ liệu
        $admins = $query->skip(($page - 1) * $num)
                       ->take($num)
                       ->get();
        $totalPages = ceil($totalAdmins / $num);
        // Trả về dữ liệu theo định dạng mong muốn (ví dụ: JSON)
        return [
            'data' => $admins,
            'total' => $totalAdmins,
            'total_page'=>$totalPages,
            'num' => $num,
            'current_page' => $page,
        ];
        } catch (Throwable $e) {
            throw new BadRequestHttpException($e->getMessage(), null, 400);
        }
    }

    public function update($id,$position,$status){
        try {
            // Tìm admin cần cập nhật
            $admin = Admin::find($id);

            // Kiểm tra xem admin có tồn tại hay không
            if (!$admin) {
                return  ['message' => 'Không tìm thấy nội dung cần update','status' => -1,'statusCode' =>400,'data'=>null];
            }

            // Cập nhật thông tin vị trí và trạng thái
            if($position!==null)
                $admin->position = $position;
            if($status!==null)
                $admin->status = $status;
            $admin->save();
            return  ['message' => 'Thành công','status' => 0,'statusCode' =>200,'data'=>$admin];
        } catch (Throwable $e) {
            throw new BadRequestHttpException($e->getMessage(), null, 400);
        }

    }

    public function updateAtribute($id,$attribute){
        try {
            $admin=Admin::findOrFail($id);
            $admin->update($attribute);
            return $admin;
        } catch (Throwable $e) {
            throw new BadRequestHttpException($e->getMessage(), null, 400);
        }
    }

    public function destroy($id){
        try {
            $admin=Admin::findOrFail($id);
            $admin->delete();
            return $admin;
        } catch (Throwable $e) {
            throw new BadRequestHttpException($e->getMessage(), null, 400);
        }
    }


}