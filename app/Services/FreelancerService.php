<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Client;
use App\Models\Freelancer;
use App\Models\Skill;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;

class FreelancerService implements IFreelancerService
{
    public function create($attributes = [])
    {
        try {
            // dd($attributes);
            return Freelancer::create($attributes);
        } catch (Throwable $e) {
            throw new BadRequestHttpException($e->getMessage(), null, 400);
        }
    }

    public function getList($num = 10, $page = 1, $searchKeyword = '', $id, $status = null, $sex = null)
    {
        try {
            // Xây dựng query Eloquent
            $query = Freelancer::query();
            if ($id) {
                $query->where('id', '=', $id);
            } elseif ($searchKeyword !== '') {
                $query->where('username', 'like', '%' . $searchKeyword . '%')
                    ->orWhere('email', 'like', '%' . $searchKeyword . '%')
                    ->orWhere('first_name', 'like', '%' . $searchKeyword . '%')
                    ->orWhere('last_name', 'like', '%' . $searchKeyword . '%');
            }
            if ($status != null)
                $query->where('status', '=',  $status);
            if ($sex != null)
                $query->where('sex', '=',  $sex);
            // Lấy tổng số admin
            $total = $query->count();

            // Thực hiện phân trang và lấy dữ liệu
            $data = $query->skip(($page - 1) * $num)
                ->take($num)
                ->get();
            $totalPages = ceil($total / $num);
            // Trả về dữ liệu theo định dạng mong muốn (ví dụ: JSON)
            return [
                'data' => $data,
                'total' => $total,
                'total_page' => $totalPages,
                'num' => $num,
                'current_page' => $page,
            ];
        } catch (Throwable $e) {
            throw new BadRequestHttpException($e->getMessage(), null, 400);
        }
    }



    public function updateAtribute($id, $attribute)
    {
        try {
            $admin = Freelancer::findOrFail($id);
            $skill = $attribute['skill'];
            unset($attribute['skill']);

            //xử lý thêm skill
            if ($skill && count($skill) > 0) {
                DB::table('skill_freelancer_map')->where('freelancer_id', $admin->id)->delete();
                foreach ($skill as $i) {
                    $tmp = DB::table('skill_freelancer_map')->insert([
                        'freelancer_id' => $admin->id,
                        'skill_id' => $i['skill_id'],
                        'skill_points' => $i['point'],
                    ]);
                }
            }



            $admin->update($attribute);
            $admin['skills'] = DB::table('skill_freelancer_map')
                ->join('skills', 'skill_freelancer_map.skill_id', '=', 'skills.id')
                ->where('skill_freelancer_map.freelancer_id', '=', $admin->id)
                ->select('skills.id as skill_id', 'skills.desc as skill_desc', 'skills.name as skill_name', 'skill_freelancer_map.skill_points')
                ->get();
            return  $admin;
        } catch (Throwable $e) {
            throw new BadRequestHttpException($e->getMessage(), null, 400);
        }
    }

    public function destroy($id)
    {
        try {
            $admin = Freelancer::findOrFail($id);
            Freelancer::destroy($id);
            return $admin;
        } catch (Throwable $e) {
            throw new BadRequestHttpException($e->getMessage(), null, 400);
        }
    }

    public function getById($id)
    {
        try {
            $admin = Freelancer::findOrFail($id);
            $admin['skills'] = DB::table('skill_freelancer_map')
                ->join('skills', 'skill_freelancer_map.skill_id', '=', 'skills.id')
                ->where('skill_freelancer_map.freelancer_id', '=', $admin->id)
                ->select('skills.id as skill_id', 'skills.desc as skill_desc', 'skills.name as skill_name', 'skill_freelancer_map.skill_points')
                ->get();
            return $admin;
        } catch (Throwable $e) {
            throw new BadRequestHttpException($e->getMessage(), null, 400);
        }
    }
}
