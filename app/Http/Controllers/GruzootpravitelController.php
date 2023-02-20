<?php

namespace App\Http\Controllers;

use App\Models\Gruzootpravitel;
use App\Models\GruzootpravitelBank;
use App\Models\GruzootpravitelContact;
use App\Models\GruzootpravitelFile;
use App\Models\Orders;
use Cassandra\Exception\ValidationException;
use Illuminate\Http\Request;

class GruzootpravitelController extends Controller
{
    public function gruzootpravitel(Request $request)
    {
        return view('front.gruzootpravitel')->with('auth_user',  auth()->user());
    }
    public function get_BIK_BANK_api(Request $request)
    {
        $bank_bik = $request->input('bank');
        $token = "24607c06c1aa996d811b3c6f4c82533b2cfcf544";
        $dadata = new \Dadata\DadataClient($token, null);
        $result = $dadata->findById("bank", $bank_bik, 1);
        if($result)
        {
            return response()->json([
                'status' => 'success',
                'bank_name' => $result[0]['value'],
                'korschet' => $result[0]['data']['correspondent_account'],
                'message' =>'Банк получен'
            ], 200);
        }
        else
        {
            return response()->json([
                'status' => 'error',
            ], 200);
        }

    }
    public function get_INN_api(Request $request)
    {
        $INN= $request->input('INN');
        $token = "24607c06c1aa996d811b3c6f4c82533b2cfcf544";
        $dadata = new \Dadata\DadataClient($token, null);
        $result = $dadata->findById("party", $INN, 1);
        if($result)
        {
            //вытаскиваем отсюда все нужные значения  https://dadata.ru/api/find-party/
            //название
            $nazvanie = $result[0]['value'] ?? "";
            //форма
            $forma = $result[0]['data']['opf']['full'] ?? "";
            //дата регистрации
            $data_registracii = date("d.m.y",($result[0]['data']['state']['registration_date'])/1000) ?? "";
            //ogrn
            $ogrn = $result[0]['data']['ogrn'] ?? "";
            //телефон, только в премиум
            $telefon = $result[0]['data']['phones'][0]['value'] ?? "";
            //email, только в премиум
            $email = $result[0]['data']['emails'][0]['value'] ?? "";
            //гендир
            $generalnii_direktor = $result[0]['data']['management']['name'] ?? "";
            //юр адрес
            $yridicheskii_adres = $result[0]['data']['address']['value'] ?? "";
            //почтовый адрес
            $pochtovyi_adres = $result[0]['data']['address']['unrestricted_value'] ?? "";
            return response()->json([
                'status' => 'success',
                'nazvanie' => $nazvanie,
                'forma' => $forma,
                'data_registracii' => $data_registracii,
                'ogrn' => $ogrn,
                'telefon' => $telefon,
                'email' => $email,
                'generalnii_direktor' => $generalnii_direktor,
                'yridicheskii_adres' => $yridicheskii_adres,
                'pochtovyi_adres' => $pochtovyi_adres,
                'res' => $result,
                'message' =>'Юр лицо получено'
            ], 200);
        }
        else
        {
            return response()->json([
                'status' => 'error',
            ], 200);
        }
    }
    public function get_gruzootpravitel_modal(Request $request)
    {
        $id = $request->input('id');
        $gruzootpravitel = Gruzootpravitel::where('id', '=', $id)->get();
        $gruzootpravitel_contact = GruzootpravitelContact::where('gruzootpravitel_id', '=', $id)->get();
        $gruzootpravitel_banks = GruzootpravitelBank::where('gruzootpravitel_id', '=', $id)->get();
        $gruzootpravitel_files = GruzootpravitelFile::where('gruzootpravitel_id', '=', $id)->get();

        return response()->json([
            'status' => 'success',
            'message' =>'Грузоотправитель успешно получен',
            'gruzootpravitel' =>$gruzootpravitel[0],
            'gruzootpravitel_banks' =>$gruzootpravitel_banks,
            'gruzootpravitel_contact' =>$gruzootpravitel_contact,
            'gruzootpravitel_files' =>$gruzootpravitel_files,
        ], 200);
    }
    public function save_gruzootpravitel(Request $request)
    {
        $current_gruzootpravitel_id = $request->input('current_gruzootpravitel_id');
        $forma = $request->input('forma');
        $nazvanie = $request->input('nazvanie');
        $data_registracii = $request->input('data_registracii');
        $INN = $request->input('INN');
        $OGRN = $request->input('OGRN');
        $telefon = $request->input('telefon');
        $email = $request->input('email');
        $generalnii_direktor = $request->input('generalnii_direktor');
        $telefon_gen_dir = $request->input('telefon_gen_dir');
        $yridicheskii_adres = $request->input('yridicheskii_adres');
        $pochtovyi_adres = $request->input('pochtovyi_adres');
        $kontakty = $request->input('kontakty');
        $bank_arr = $request->input('bank_arr');
        $doc_files = $request->input('doc_files');


        $rules = [
            'nazvanie' => 'required','unique:users'
        ];
        $request->validate($rules);



        //если новое модальное окно
             if($current_gruzootpravitel_id=='')
             {
                 $gruz_main= Gruzootpravitel::create([
                     'forma_id' => $forma,
                     'nazvanie' => $nazvanie,
                     'data_registracii' => $data_registracii,
                     'INN' => $INN,
                     'OGRN' => $OGRN,
                     'telefon' => $telefon,
                     'email' => $email,
                     'generalnii_direktor' => $generalnii_direktor,
                     'telefon_gen_dir' => $telefon_gen_dir,
                     'YR_adres' => $yridicheskii_adres,
                     'pochtovyi_adres' => $pochtovyi_adres,
                 ]);
                 if($kontakty)
                 {
                     foreach ($kontakty as $kontakt)
                     {
                         GruzootpravitelContact::create([
                             'gruzootpravitel_id' => $gruz_main['id'],
                             'dolznost' => $kontakt['dolznost'],
                             'FIO' => $kontakt['FIO'],
                             'telefon' => $kontakt['telefon'],
                             'email' => $kontakt['email'],
                         ]);
                     }
                 }
                 if($bank_arr)
                 {
                     foreach ($bank_arr as $bank)
                     {
                         GruzootpravitelBank::create([
                             'gruzootpravitel_id' => $gruz_main['id'],
                             'BIK' => $bank['BIK'],
                             'raschetnyi' => $bank['raschetnyi'],
                             'korschet' => $bank['korschet'],
                             'kommentarii' => $bank['kommentarii'],
                             'bank' => $bank['bank'],
                         ]);
                     }
                 }
                 if($doc_files)
                 {
                     foreach ($doc_files as $doc)
                     {
                         if($doc['doc_path']!='')
                         {
                             GruzootpravitelFile::create([
                                 'gruzootpravitel_id' => $gruz_main['id'],
                                 'file_name' => $doc['file_name'].'.'.$doc['ext'],
                                 'server_name' => $doc['doc_path'],
                             ]);
                         }
                     }
                 }
                 return response()->json([
                     'status' => 'success',
                     'message' =>'Успешно сохранено',
                 ], 200);
             }
             //если редактируем уже существуюющие модальное окно
             else
             {
//                 return dd($current_gruzootpravitel_id);
                 Gruzootpravitel::where('id', '=',$current_gruzootpravitel_id)->update([
                     'forma_id' => $forma,
                     'nazvanie' => $nazvanie,
                     'data_registracii' => $data_registracii,
                     'INN' => $INN,
                     'OGRN' => $OGRN,
                     'telefon' => $telefon,
                     'email' => $email,
                     'generalnii_direktor' => $generalnii_direktor,
                     'telefon_gen_dir' => $telefon_gen_dir,
                     'YR_adres' => $yridicheskii_adres,
                     'pochtovyi_adres' => $pochtovyi_adres,
                 ]);
                 GruzootpravitelContact::where('gruzootpravitel_id', '=',$current_gruzootpravitel_id)->delete();
                 GruzootpravitelBank::where('gruzootpravitel_id', '=',$current_gruzootpravitel_id)->delete();
                 if($kontakty)
                 {
                     foreach ($kontakty as $kontakt)
                     {
                         GruzootpravitelContact::create([
                             'gruzootpravitel_id' => $current_gruzootpravitel_id,
                             'dolznost' => $kontakt['dolznost'],
                             'FIO' => $kontakt['FIO'],
                             'telefon' => $kontakt['telefon'],
                             'email' => $kontakt['email'],
                         ]);
                     }
                 }
                 if($bank_arr)
                 {
                     foreach ($bank_arr as $bank)
                     {
                         GruzootpravitelBank::create([
                             'gruzootpravitel_id' => $current_gruzootpravitel_id,
                             'BIK' => $bank['BIK'],
                             'raschetnyi' => $bank['raschetnyi'],
                             'korschet' => $bank['korschet'],
                             'kommentarii' => $bank['kommentarii'],
                             'bank' => $bank['bank'],
                         ]);
                     }
                 }
//                 GruzootpravitelFile::where('gruzootpravitel_id', '=',$current_gruzootpravitel_id)->delete();

                 if($doc_files)
                 {
                     //удаляем все старые документы которых нет в массиве приходящего с фронта
                     $old_docs=GruzootpravitelFile::where('gruzootpravitel_id', '=',$current_gruzootpravitel_id)->get();
                     //старый массив в БД который
                     foreach ($old_docs as $old_doc)
                     {
                         $flag=false;
                         //приходящий массив
                         foreach ($doc_files as $doc)
                         {
                             if($doc['id']!=null)
                             {
                                 if($old_doc['id']==$doc['id'])
                                 {
                                     $flag=true;
                                 }
                             }
                         }

                         //если нету в новом массиве тогда удаляем и файл и из БД
                         if($flag==false)
                         {
                             try {
                                 $path_to_del = public_path() . "/modal/" . $old_doc['server_name'];
                                 unlink($path_to_del);
                                 GruzootpravitelFile::where('id', '=',$old_doc['id'])->delete();
                             }
                             catch (\Throwable $e)
                             {

                             }
                         }
                     }
                     foreach ($doc_files as $doc)
                     {
                         //если новый док и не пустой
                         if(($doc['id']==null)&&($doc['doc_path']!=null))
                         {
                             GruzootpravitelFile::create([
                                 'gruzootpravitel_id' => $current_gruzootpravitel_id,
                                 'file_name' => $doc['file_name'].'.'.$doc['ext'],
                                 'server_name' => $doc['doc_path'],
                             ]);
                         }
                         if($doc['id']!=null)
                         {
                             GruzootpravitelFile::where('id', '=',$doc['id'])->update([
                                 'file_name' => $doc['file_name'],
                             ]);
                         }

                     }
                 }
                 //если массив приходящий пустой но там в БД лежали старые файлы, то их нужно удалить
                 else
                 {
                     $old_docs=GruzootpravitelFile::where('gruzootpravitel_id', '=',$current_gruzootpravitel_id)->get();
                     foreach ($old_docs as $old_doc)
                     {
                         try {
                             $path_to_del = public_path() . "/modal/" . $old_doc['server_name'];
                             unlink($path_to_del);
                             GruzootpravitelFile::where('id', '=',$old_doc['id'])->delete();
                         }
                         catch (\Throwable $e)
                         {

                         }
                     }
                 }
                 return response()->json([
                     'status' => 'success',
                     'message' =>'Успешно сохранено',
                 ], 200);
             }

    }
    //пока не используется этот метод
    public function delete_one_file_modal(Request $request)
    {
        $doc = $request->input('doc');

        try {
            $path_to_del = public_path() . "/modal/" . $doc['doc_path'];
            unlink($path_to_del);

        }
        catch (\Throwable $e)
        {

        }
    }
    public function download_modal_file($doc)
    {
        $filetopath=public_path("/modal/".$doc);
        return response()->download($filetopath);
    }
    public function delete_files_modal(Request $request)
    {
        $doc_files = $request->input('doc_files');

        if($doc_files)
        {
        foreach ($doc_files as $doc)
        {

        try {
                $path_to_del = public_path() . "/modal/" . $doc['doc_path'];
                unlink($path_to_del);
            }
            catch (\Throwable $e)
            {

            }
            }
            }
    }
    public function store_modal_file_temp(Request $request)
    {
        $file_name = $request->input('file_name');
        $extension = $request->input('extension');

        $path=time().'_'.$request['file_name'].'.'.$request['extension'];
//        $doc_in_db=GruzootpravitelFile::
//        create([
//            'gruzootpravitel_id' => $gruz_main['id'],
//            'BIK' => $bank['BIK'],
//            'raschetnyi' => $bank['raschetnyi'],
//            'korschet' => $bank['korschet'],
//            'kommentarii' => $bank['kommentarii'],
//            'bank' => $bank['bank'],
//        ]);
//        if (!$doc_in_db->isEmpty()) {
//            try {
//                $path_to_del = public_path() . "/grade_doc/" . $doc_in_db[0]['path_doc'];
//                unlink($path_to_del);
//            }
//            catch (\Throwable $e)
//            {
//
//            }
//        }
        $request['file']->move(public_path('/modal/'), $path);
        return response()->json([
            'status' => 'success',
            'message' =>'Временный файл modal успешно сохранён',
            'path' =>$path
        ], 200);
    }

    public function get_gruzootpravitel_list()
    {
        $gruzootpravitel = Gruzootpravitel::all();
        return response()->json([
            'status' => 'success',
            'gruzootpravitel' =>$gruzootpravitel,
        ], 200);
    }
    public function get_gruzootpravitel_list_atocomplite(Request $request)
    {
        $queryString=$request->input('req');
        $gruzootpravitels = Gruzootpravitel::where('nazvanie', 'LIKE', "%$queryString%")->orderBy('id')->get();
        return $gruzootpravitels;
    }

    public function get_gruzootpravitel_list_front(Request $request)
    {
        $offset =  $request->input('offset');
        $limit =  $request->input('limit');

        //получаем количество всех важных записей
        $all_imp = Gruzootpravitel::where('important', 1) ->count();

        //получаем все важные сначала
        $list_colored_imp = Gruzootpravitel::where('important', 1)

            ->get();
//        $gruzootpravitel = Gruzootpravitel::where('id', '>', 0)->get();
        $not_empty_flag=false;

        if($all_imp>$offset)
        {
            $dif=$limit+$offset-$all_imp;

            if($dif>0)
            {
                if($all_imp-$offset>0)
                {
                    $temp_offset=0;
                }
                //получаем все не важные записи с листа
                $list_colored = Gruzootpravitel::where('id', '>=', 0)
                    ->where('important',null)
                    ->offset($temp_offset)
                    ->limit($dif)
                    ->get();
                $not_empty_flag=true;
            }
        }
        else
        {
            $list_colored = Gruzootpravitel::where('id', '>=', 0)
                ->where('important',null)
                ->offset($offset-$all_imp)
                ->limit($limit)
                ->get();
            $not_empty_flag=true;
        }

        $res_arr=[];
        if($all_imp>$offset)
        {
            //добавляем важные записи в результирующий массив
            foreach ($list_colored_imp as $imp)
            {
                array_push($res_arr,$imp);
            }
            $res_arr = array_splice($res_arr, $offset, $limit);
        }
        if($not_empty_flag)
        {
            //добавляем все остальные записи в результирующий массив
            foreach ($list_colored as $colored)
            {
                array_push($res_arr,$colored);
            }
        }
        $count = Gruzootpravitel::where('id', '>=', 0)
            ->count();

        foreach ($res_arr as $gruz)
        {
            $kontaktnoe_lico_arr = GruzootpravitelContact::where('gruzootpravitel_id', '=', $gruz['id'])->get();
            $gruz['kontaktnoe_lico']=$kontaktnoe_lico_arr;
        }
        return response()->json([
            'status' => 'success',
            'gruzootpravitel' => $res_arr,
            'tipes_count' => $count
        ], 200);
    }
    public function delete_gruzootpravitel(Request $request)
    {
        $gruzootpravitels_id = $request->input('gruzootpravitels_id');
        foreach ($gruzootpravitels_id as $gruzootpravitel_id)
        {
        $old_docs=GruzootpravitelFile::where('gruzootpravitel_id', '=',$gruzootpravitel_id)->get();
        foreach ($old_docs as $old_doc)
        {
            try {
                $path_to_del = public_path() . "/modal/" . $old_doc['server_name'];
                unlink($path_to_del);
                GruzootpravitelFile::where('id', '=',$old_doc['id'])->delete();
            }
            catch (\Throwable $e)
            {

            }
        }
        GruzootpravitelContact::where('gruzootpravitel_id', '=',$gruzootpravitel_id)->delete();
        GruzootpravitelBank::where('gruzootpravitel_id', '=',$gruzootpravitel_id)->delete();
        Gruzootpravitel::where('id', '=',$gruzootpravitel_id)->delete();
        }
        return response()->json([
            'status' => 'success',
            'message' =>'Грузоотправители удалены',
        ], 200);
    }
    public function select_gruzootpravitel()
    {
        $gruzootpravitel=  Gruzootpravitel::latest('id')->first();
        return response()->json([
            'status' => 'success',
            'message' =>'Грузоотправитель получен',
            'gruzootpravitel' =>$gruzootpravitel,
        ], 200);
    }
    public function check_if_name_gruz(Request $request)
    {
        $order_id =  $request->input('order_id');
        $adres_pogruzke_show =  $request->input('adres_pogruzke_show');
        $column_name =  $request->input('column_name');
        $gruz= Gruzootpravitel::where('nazvanie', '=',$adres_pogruzke_show)->get();
        if($adres_pogruzke_show=='')
        {
            Orders::where('id', $order_id)->update([
                $column_name =>null,
            ]);
            return response()->json([
                'status' => 'success',
                'message' =>'убрали грузоотправителя',
                'isset_flag' =>'no',
                'adres_pogruzke' =>0,
            ], 200);
        }
        if (!$gruz->isEmpty()) {
            Orders::where('id', $order_id)->update([
                $column_name =>$gruz[0]['id'],
            ]);
            return response()->json([
                'status' => 'success',
                'message' =>'Грузоотправитель изменен',
                'isset_flag' =>'yes',
                'adres_pogruzke' =>$gruz[0]['id'],
            ], 200);
        }
        return response()->json([
            'status' => 'success',
            'message' =>'Нет такого грузоотправителя',
            'isset_flag' =>'no',
            'adres_pogruzke' =>0,
        ], 200);

    }
    public function check_if_name_gruz_isset(Request $request)
    {
        $adres_pogruzke_show =  $request->input('adres_pogruzke_show');
        $gruz= Gruzootpravitel::where('nazvanie', '=',$adres_pogruzke_show)->get();
        if($adres_pogruzke_show=='')
        {
            return response()->json([
                'status' => 'success',
                'message' =>'убрали грузоотправителя',
                'isset_flag' =>'no',
                'adres_pogruzke' =>0,
            ], 200);
        }
        if (!$gruz->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' =>'Грузоотправитель получен',
                'isset_flag' =>'yes',
                'adres_pogruzke' =>$gruz[0]['id'],
            ], 200);
        }
        return response()->json([
            'status' => 'success',
            'message' =>'Нет такого грузоотправителя',
            'isset_flag' =>'no',
            'adres_pogruzke' =>0,
        ], 200);
    }
}
