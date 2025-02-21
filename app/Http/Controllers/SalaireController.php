<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use  App\Models\ReglesSalaire;
use  App\Models\Compte;
//use Illuminate\Support\Facades\File;
use File;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use DB;
//use GuzzleHttp\Psr7\Request;

class SalaireController extends Controller
{

    
    public function get($id = 1)
    {
        $regle = ReglesSalaire::find($id);
        
        return view('get',["regle"=>$regle]);
    }
    public function get_sapce($nb){
        $l = "";
        for ($i=1; $i<=$nb  ; $i++) {
            $l .=" ";
        }
        return $l;
    }

    public function get_0($salaire){
      $nb=  strlen(trim($salaire.''));
      $rest = 20-$nb;
      $zeoro = '';
      for ($i=1; $i <$rest ; $i++) {
           $zeoro .='0';
      }

      return $zeoro.trim($salaire.'');
    }

    public function store(Request $request)
    {
        $request->validate([
            'fichier' => 'required',
        ],
         ["fichier.required"=>"Assurez-vous de spécifier un fichier"]);
        //$path_dest = "C:\Users\Administrateur\Desktop\\veth\mauri.txt"; 
        $regle = ReglesSalaire::find($request->id);
       
         $response=   $this->salaire_rim($request->file('fichier')->path() , $regle);
        $res = $response[0];
        if($res->count()){
            return response()->json(["id"=>0,'response'=>$res->toArray() , 'montant' =>$response[1] , 'status'=>false]);
        }else
         return response()->json(["id"=>$regle->id ,'response'=>$res->toArray() , 'montant' =>$response[1] , 'status'=>true]);
           
    }

    public function dowload_file($id){
        $regle = ReglesSalaire::find($id);
        return response()->download(public_path('move/'.$regle->name_file. '.txt'));
    }

    // public function  api(){
       

    //     $params=array(
    //     'token' => '455545ffd',
    //     'to' => '+22248959774',
    //     'body' => 'WhatsApp API on UltraMsg.com works good'
    //     );

    //     $client = new Client();
    //     $headers = [
    //     'Content-Type' => 'application/x-www-form-urlencoded'
    //     ];
    //     $options = ['form_params' =>$params ];
    //     $request = new Request('POST', 'https://api.ultramsg.com/{INSTANCE_ID}/messages/chat', $headers);
    //     $res = $client->sendAsync($request, $options)->wait();
    //     echo $res->getBody();
    // }

    public function get_montant ($path , $regle){
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
         $spreadsheet = $reader->load($path);
        // $last_th = null;:
         $worksheet = $spreadsheet->getActiveSheet();
         $espace = explode(',',$regle->nbespace);
        $ordre = explode(',',$regle->ordre);
        $i = 0;
        $montant = 0;
        $debutFile = false;
        $debut_trans = true;
        $ligne = '';
        //$ligne .='1';
        $ligne .=$regle->entete;
        
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            
            $r = '';
            $i = 0;
            foreach ($cellIterator as $cell) {
                if($i == 0) $r .= $cell->getCalculatedValue();
                else $r .=';'.$cell->getCalculatedValue();
                $i++;
            }
            
            $ligne = '';
            $chaine = explode(';' , $r);
            if(isset($chaine[0])  && trim($chaine[0]) === trim($ordre[0])){
                $debutFile = true;
            }
            elseif( $debutFile == true && $chaine[0] != '' )  {
                //dd($chaine[$regle->montant]);
                $montant += $chaine[$regle->montant];
            }
               
        }
        return $montant*100;
    }
    public function salaire_rim($path , $regle){
        $data = collect();
        //$path_dest = 'C:\Users\Administrateur\Desktop\\veth\\'.$regle->name_file. '.txt';
        //dd($path_dest);
        $path_dest = public_path("move/".$regle->name_file. '.txt');
        //dd($regle);
        $espace = explode(',',$regle->nbespace);
        $ordre = explode(',',$regle->ordre);
         $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
         $spreadsheet = $reader->load($path);
        // $last_th = null;:
         $worksheet = $spreadsheet->getActiveSheet();
        $i = 0;
        $debutFile = false;
        $debut_trans = true;
        $ligne = '';
        //$ligne .='1';
        $montant = $this->get_montant($path , $regle);
        $ligne .=  str_replace("montant", "'".$montant."'", $regle->entete) ;
        File::put($path_dest, $ligne."\n");          
        $error = false;
        $data_compte = Collect();
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            
            $r = '';
            $i = 0;
            foreach ($cellIterator as $cell) {
                if($i == 0) $r .= $cell->getCalculatedValue();
                else $r .=';'.$cell->getCalculatedValue();
                $i++;
            }
            
            $ligne = '';
            $chaine = explode(';' , $r);
            if(isset($chaine[0]) && trim($chaine[0]) === trim($ordre[0])){
                $debutFile = true;
            }
            elseif( $debutFile == true && $chaine[0] != '' )  {
                $detaillCompte = Compte::where( 'ncp'  , $chaine[$regle->compte])->first();
                //$this->get_formation_compte($chaine[$regle->compte]);
                if($detaillCompte  && !$error){
                    $dtcomp = '00013'.$detaillCompte->age.$detaillCompte->ncp;
                    //dd();
                    $ligne ='2'.$this->get_sapce($espace[0]);
                    $ligne .="A".$this->get_sapce($espace[1] - strlen("A"));
                    $ligne .='ABM'.$this->get_sapce($espace[2]- strlen('ABM'));
                    $ligne .=trim($dtcomp).$this->get_sapce($espace[3]- strlen(trim($dtcomp)));
                    $sl = trim($detaillCompte->clc).$this->get_0($chaine[$regle->montant]*100).'VIREMENT';
                    $ligne .=$sl.$this->get_sapce($espace[4]- strlen($sl));
                    $ligne .=$regle->nom_virement;
                    File::append($path_dest, $ligne."\n");
                }
                else{
                    $error = true;
                    if(!$detaillCompte){
                        $data_compte->push($chaine[$regle->compte]);

                    }

                }
                
            }
            $i++;   
        }

        return  [$data_compte , $montant]  ;
        
        //File::move($path_dest, public_path("move/".$regle->name_file. '.txt'));
        
    }

    public function get_formation_compte($compte){
        $script = str_replace("compte", "'".$compte."'", $this->requete);
        ini_set('max_execution_time', 9000);
        $req = DB::connection('oracle')->select($script);
          return $req;
    }

    public function save_comptes(){

        ini_set('max_execution_time', 9000);
        $req = DB::connection('oracle')->select($this->bkcom);
        Compte::query()->delete();
        //dd(json_decode(json_encode($req)));
        foreach ($req as $key => $value) {
           $cmp = new Compte();
           $cmp->age = trim($value->age);
           $cmp->ncp = trim($value->ncp);
           $cmp->clc = trim($value->clc);
           $cmp->save();
        }
       //ompte::insert($req->toArray());
       
    }

    private $requete = "select unique rpad('00013'||trim(a.age)||trim(a.ncp),26,' ') as dtcom, clc from prod.bkcom a where a.cfe='N' and a.ife='N' and a.ncp in (compte)";
    private $bkcom = "select unique a.age ,  a.ncp,  a.clc from prod.bkcom a where (a.cfe='N' and a.ife='N' and a.cpro in ('001','002','003','011','012','013','014','021','024') and dev='929') or ncp = '90000001467'" ;

   
   

   
}
