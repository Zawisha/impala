<?php

namespace App\Http\Controllers;

use App\Models\FormaModal;
use App\Models\OrdersPerevozchiki;
use App\Models\PogruzkaTS;
use App\Models\Terminal;
use App\Models\TS;
use App\Models\TypePer;
use App\Models\VidTS;
use Illuminate\Http\Request;

class TSController extends Controller
{

    protected $TSModel;
    protected $vidTSModel;
    protected $ordersPerevozchiki;

    public function __construct(
        TS $TSModel,
        VidTS $vidTSModel,
        OrdersPerevozchiki $ordersPerevozchiki ,
    )
    {
        $this->TSModel = $TSModel;
        $this->vidTSModel = $vidTSModel;
        $this->ordersPerevozchiki = $ordersPerevozchiki;
    }

    public function get_terminal_list()
    {
        $TS_list= Terminal::all();
        return response()->json([
            'status' => 'success',
            'message' =>'Список названий ТС получен',
            'terminal' =>$TS_list,
        ], 200);
    }
    public function get_type_per_list()
    {
        $TS_list= TypePer::all();
        return response()->json([
            'status' => 'success',
            'message' =>'Список названий ТС получен',
            'ts' =>$TS_list,
        ], 200);
    }
    public function get_ts_list(Request $request)
    {
        $TS_list= VidTS::all();
        return response()->json([
            'status' => 'success',
            'message' =>'Список названий ТС получен',
            'ts' =>$TS_list,
        ], 200);
    }
    public function get_forma_list(Request $request)
    {
        $TS_list= FormaModal::all();
        return response()->json([
            'status' => 'success',
            'message' =>'Список названий ТС получен',
            'ts' =>$TS_list,
        ], 200);
    }
    public function save_ts(Request $request)
    {
        $id_ts=$request->input('id_ts');
        $order_id=$request->input('order_id');
        $vid_TS=$request->input('vid_TS');
        $stavka_TS=$request->input('stavka_TS');
        $stavka_TS_za_km=$request->input('stavka_TS_za_km');
        $stavka_kp_TS=$request->input('stavka_kp_TS');
        $marja_TS=$request->input('marja_TS');
        $kol_gruz_TS=$request->input('kol_gruz_TS');
        $kol_TS_TS=$request->input('kol_TS_TS');
        $rasstojanie_TS=$request->input('rasstojanie_TS');
        $adres_pogruzki_TS=$request->input('adres_pogruzki_TS');
        $ob_ves_TS=$request->input('ob_ves_TS');
        $ob_ob_TS=$request->input('ob_ob_TS');
        $adres_vygr_TS=$request->input('adres_vygr_TS');
        $kommentari_TS=$request->input('kommentari_TS');
        $checked2=$request->input('checked2');
        $terminal_TS=$request->input('terminal_TS');
        $stavka_TS_bez_NDS=$request->input('stavka_TS_bez_NDS');
//        return dd($adres_pogruzki_TS);

//если создаём новое ТС
        $TS_list= TS::where('order_id', '=', $order_id)->where('id_ts', '=', $id_ts)->get();
        if($TS_list->isEmpty())
        {
          $TS= TS::create([
                'id_ts' =>$id_ts,
                'order_id' =>$order_id,
                'vid_TS' =>$vid_TS,
                'stavka_TS' =>$stavka_TS,
                'stavka_TS_za_km' =>$stavka_TS_za_km,
                'stavka_kp_TS' =>$stavka_kp_TS,
                'marja_TS' =>$marja_TS,
                'kol_gruz_TS' =>$kol_gruz_TS,
                'kol_TS_TS' =>$kol_TS_TS,
                'rasstojanie_TS' =>$rasstojanie_TS,
                'ob_ves_TS' =>$ob_ves_TS,
                'ob_ob_TS' =>$ob_ob_TS,
                'kommentari_TS' =>$kommentari_TS,
                'checked2' =>$checked2,
                'terminal_TS' =>$terminal_TS,
                'stavka_TS_bez_NDS' =>$stavka_TS_bez_NDS,
            ]);
            //добавим перевозчиков к ТС
            foreach (request('perevozchikiList') as $onePerevozchik)
            {
                $onePerevozchik['stavka_za_km'] = $onePerevozchik['stavka_za_km'] ?? 0;
                $this->ordersPerevozchiki->addPerevozchik($TS->id,$onePerevozchik['perevozchik_id'],$onePerevozchik['stavka_NDS'],$onePerevozchik['stavka_bez_NDS'],$onePerevozchik['stavka_za_km']);
            }
            PogruzkaTS::where('id_ts' ,$id_ts,)->where('order_id',$order_id)->delete();
            foreach ($adres_pogruzki_TS as $adres)
            {
                PogruzkaTS::create([
                    'id_ts' =>$id_ts,
                    'order_id' =>$order_id,
                    //погрузка 1 выгрузка 2
                    'pogruzka_or_vygruzka' =>'1',
                    'adres_pogruzki' =>$adres['adres_pogruzki'],
                ]);
            }
            foreach ($adres_vygr_TS as $adres)
            {
                PogruzkaTS::create([
                    'id_ts' =>$id_ts,
                    'order_id' =>$order_id,
                    //погрузка 1 выгрузка 2
                    'pogruzka_or_vygruzka' =>'2',
                    'adres_pogruzki' =>$adres['adres_pogruzki'],
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' =>'ТС сохранено',
            ], 200);
        }
        //если редактируем существующее
        else
        {
          TS::where('order_id', '=', $order_id)->where('id_ts', '=', $id_ts)->update([
                'vid_TS' =>$vid_TS,
                'stavka_TS' =>$stavka_TS,
                'stavka_TS_za_km' =>$stavka_TS_za_km,
                'stavka_kp_TS' =>$stavka_kp_TS,
                'marja_TS' =>$marja_TS,
                'kol_gruz_TS' =>$kol_gruz_TS,
                'kol_TS_TS' =>$kol_TS_TS,
                'rasstojanie_TS' =>$rasstojanie_TS,
                'ob_ves_TS' =>$ob_ves_TS,
                'ob_ob_TS' =>$ob_ob_TS,
                'kommentari_TS' =>$kommentari_TS,
                'checked2' =>$checked2,
                'terminal_TS' =>$terminal_TS,
                'stavka_TS_bez_NDS' =>$stavka_TS_bez_NDS,
            ]);
            //добавим перевозчиков к ТС
            //сначала удалим старых
            $this->ordersPerevozchiki->deletePerevozchikFromOrderByOrder($TS_list[0]->id);
            foreach (request('perevozchikiList') as $onePerevozchik)
            {
                $onePerevozchik['stavka_za_km'] = $onePerevozchik['stavka_za_km'] ?? 0;
                $this->ordersPerevozchiki->addPerevozchik($TS_list[0]->id,$onePerevozchik['perevozchik_id'],$onePerevozchik['stavka_NDS'],$onePerevozchik['stavka_bez_NDS'],$onePerevozchik['stavka_za_km']);
            }

            PogruzkaTS::where('id_ts' ,$id_ts,)->where('order_id',$order_id)->delete();
            foreach ($adres_pogruzki_TS as $adres)
            {
                PogruzkaTS::create([
                    'id_ts' =>$id_ts,
                    'order_id' =>$order_id,
                    //погрузка 1 выгрузка 2
                    'pogruzka_or_vygruzka' =>'1',
                    'adres_pogruzki' =>$adres['adres_pogruzki'],
                ]);
            }
            foreach ($adres_vygr_TS as $adres)
            {
                PogruzkaTS::create([
                    'id_ts' =>$id_ts,
                    'order_id' =>$order_id,
                    //погрузка 1 выгрузка 2
                    'pogruzka_or_vygruzka' =>'2',
                    'adres_pogruzki' =>$adres['adres_pogruzki'],
                ]);
            }
        }

    }

    public function delete_TS(Request $request)
    {
        $id_ts=$request->input('id_ts');
        $order_id=$request->input('order_id');
        $TS=TS::where('order_id', '=', $order_id)->where('id_ts', '=', $id_ts)->get();
        if (!$TS->isEmpty())
        {
            $this->ordersPerevozchiki->deletePerevozchikFromOrderByOrder($TS[0]->id);
        }
        PogruzkaTS::where('id_ts' ,$id_ts,)->where('order_id',$order_id)->delete();
        TS::where('order_id', '=', $order_id)->where('id_ts', '=', $id_ts)->delete();
    }

    public function get_vid_TS_list()
    {
        $TS_list=$this->vidTSModel->getVidTSList(request('offset'),request('limit'));
        $tipesCount=$this->vidTSModel->countVidTs();
        return response()->json([
            'status' => 'success',
            'ts' =>$TS_list,
            'tipesCount' =>$tipesCount,
            'message' =>'Вид ТС создан',
        ], 200);
    }

    //метод удаления Вида ТС
    public function delete_vid_TS(Request $request)
    {
        //удаляем(ставим NULL) из таблицы уже существующих ТС ( не видов а самих ТС )
        $this->TSModel->setNullVidTS(request('vidTSId'));
        //удаляем из таблицы vid_TS сам vid_TS
        $this->vidTSModel->deleteVidTS(request('vidTSId'));
        return response()->json([
            'status' => 'success',
            'message' =>'Вид ТС удалено',
        ], 200);
    }
    public function update_vid_TS(Request $request)
    {
        $this->vidTSModel->updateVidTS(request('vidTSId'),request('ts_name'));
        return response()->json([
            'status' => 'success',
            'message' =>'Вид ТС обновлён',
        ], 200);
    }
    public function create_vid_TS()
    {
       $result=$this->vidTSModel->createVidTS();
        return response()->json([
            'status' => 'success',
            'id' => $result['id'],
            'message' =>'Вид ТС создан',
        ], 200);
    }
    public function getVidTSNazvanie(Request $request)
    {
        $ts=$this->vidTSModel->getVidTSNazvanie(request('ts_name'));
        if (!$ts->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' =>'Вид ТС получен',
                'isset_flag' =>'yes',
                'idTSBack' =>$ts[0]['id'],
            ], 200);
        }
        return response()->json([
            'status' => 'success',
            'message' =>'Нет такого вида ТС',
            'isset_flag' =>'no',
            'adres_pogruzke' =>0,
        ], 200);
    }

}
