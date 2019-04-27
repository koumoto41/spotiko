<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Token
 * @package App\Models
 */
class Token extends Model
{
    protected $table = 'token';
    protected $guarded = ['id'];
    protected $fillable = ['use_id', 'token'];
    public $timestamps = true;

    /**
     * Token情報を全て取得
     * @return mixed
     */
    public function get_data() {
        $data = DB::table($this->table)->get();
        return $data->toArray;
    }

    /**
     * $user_idで絞り込んだToken情報を取得
     * @param $user_id
     * @return Model|\Illuminate\Database\Query\Builder|null|object
     */
    public function get_data_by_user_id($user_id) {
        $data = DB::table($this->table)->where('user_id', $user_id)->first();
        return $data;
    }

    /**
     * Token情報をinsert
     * @param $user_id
     * @param $token
     * @param $refresh_token
     */
    public function set_token($user_id, $token, $refresh_token) {
        $token_model = new Token;
        $token_model->user_id = $user_id;
        $token_model->token = $token;
        $token_model->refresh_token = $refresh_token;
        $token_model->save();
    }

    /**
     * Token情報を削除
     * @param $user_id
     */
    public function delete_token($user_id) {
        DB::table($this->table)->where('user_id', $user_id)->delete();
    }
}
