<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use  App\Models\ReglesSalaire;
use  App\Models\Compte;
use  App\Models\Historique;
use  App\Models\Matricule;
//use Illuminate\Support\Facades\File;
use File;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SalaireController extends Controller
{

    public function index(){
        
        $historiques = Historique::where("date" , Carbon::now()->format('Y-m-d'))->get();
        return view('index' , ['histo_this_days'=>$historiques]);
    }
    public function refrech_data(){
        $historiques = Historique::where("date" , Carbon::now()->format('Y-m-d'))->get();
        return view('ajax.ligne' , ['histo_this_days'=>$historiques]);
    }
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
       $nbtotal = strlen($zeoro.trim($salaire.''));
       if($nbtotal == 20)
            return $zeoro.trim($salaire.'');
        elseif($nbtotal <20)
             {
                for ($i=1; $i <=20-$nbtotal ; $i++) {
                    $zeoro .='0';
               }
                return $zeoro.trim($salaire.'');
             }
    }

    public function snimBanque($path = "S:\Organisation&Informatique\Projets\jobs virements de masse\\fichiers valides\SNIM2.0\snimsalaire.txt"){
        //dd($path);
        $lines = $this->read_text($path);
        if($lines == null)
            return  "le fichier selectionnne ne contient pas d'information";
        else{
            foreach ($lines as $key => $ligne) {
                   $matricule =   substr($ligne, 0, 8);
                   $compte = Matricule::where();
                echo $ligne ."<br>";
            }
        }
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
        }else{
            $histo =  new Historique;
            $histo->date = Carbon::now()->format('Y-m-d');
            $histo->user_id = Auth::user()->id;
            $histo->regles_salaire_id = $regle->id;
            $histo->montant_Total = $response[1];
            $histo->nbres_lignes = $response[2];
            $file = $request->file('fichier');
            
            $histo->save();
            $fileID = 'fichier-'.$histo->id.'.' . $file->getClientOriginalExtension();
            $file->move(public_path('assets/file'), $fileID);
            $histo->path_file = 'assets/files/'.$fileID;
            $histo->save();

            return view('success' , [ "route"=>route('download' , $regle->id) ,  'montant'=> $response[1] , 'regles'=>$regle ,  'lignes'=>$response[2]] );
            //return response()->json(["id"=>$regle->id ,'response'=>$res->toArray() , 'montant' =>$response[1] , 'status'=>true]);

        }
           
    }

    public function dowload_file($id){
        $regle = ReglesSalaire::find($id);
        return response()->download(public_path('move/'.$regle->name_file. '.txt'));
    }

    public function historiques(){
        $historiques = Historique::where("date" , Carbon::now()->format('Y-m-d'))->get();
        return view('this_day',["historiques"=>$historiques , "date"=>Carbon::now()->format('Y-m-d')]);

    }


    public function read_text($path){
            
            $lines = File::lines($path);  // Spécifiez le chemin du fichier
            //dd($path);
            //$lines = File::get($file);  // Récupère tout le contenu du fichier
            // Diviser le contenu en lignes
            //$lines = explode(PHP_EOL, $lines);
            return $lines;
        
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

    public function get_montant($path , $regle){
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
         $spreadsheet = $reader->load($path);
        // $last_th = null;:
         $worksheet = $spreadsheet->getActiveSheet();
         $espace = explode(',',$regle->nbespace);
        $ordre = explode(',',$regle->ordre);
        $i = 0;
        $montant = 0;
        $debutFile = false;
        //$ligne = '';
        //$ligne .='1';
       // $ligne .=$regle->entete;
        $j = 0;
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            
            $r = '';
            $i= 0;
            foreach ($cellIterator as $cell) {
                if($i == 0) $r .= $cell->getCalculatedValue();
                else $r .=';'.$cell->getCalculatedValue();
                $i++;
            }
            
            $ligne = '';
            $chaine = explode(';' , $r);
            if( $debutFile ==false &&  isset($chaine[0])  && trim($chaine[0]) === trim($ordre[0])){
                $debutFile = true;
                //dd($chaine[$regle->montant]);
            }
            elseif( $debutFile == true && $chaine[0] != '' )  {
                //dd($chaine[$regle->montant]);
                $montant += $chaine[$regle->montant];
                $j++;
            }
               
        }
        //dd([$montant*100 , $j]);
        return  [$montant*100 , $j] ;
    }
    public function salaire_rim($path , $regle){
        $data = collect();
        $path_dest = public_path("move/".$regle->name_file. '.txt');
        $espace = explode(',',$regle->nbespace);
        $ordre = explode(',',$regle->ordre);
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($path);
        $worksheet = $spreadsheet->getActiveSheet();
        $i = 0;
        $debutFile = false;
        $debut_trans = true;
        //$rows = '';
        $ligne ='';
        $d = $this->get_montant($path , $regle);
        $montant =$d[0]; 
        $rows =$d[1];
        $mont = trim($regle->cle_entete).$this->get_0($montant); 
        $ligne =  str_replace("montant", $mont, $regle->entete) ;
        $ligne =  str_replace("date", Carbon::now()->format('dmy'), $ligne) ;
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
                    //dd($espace[0]);
                    $ligne ='2'.$this->get_sapce( (int) $espace[0] -strlen("2") );
                    $ligne .="A".$this->get_sapce( (int) $espace[1] - strlen("A"));
                    $ligne .='ABM'.$this->get_sapce( (int) $espace[2]- strlen('ABM'));
                    $ligne .=trim($dtcomp).$this->get_sapce($espace[3]- strlen(trim($dtcomp)));
                    $sl = trim($detaillCompte->clc).$this->get_0( (int) $chaine[$regle->montant]*100).'VIREMENT';
                    $ligne .=$sl.$this->get_sapce( (int) $espace[4]- strlen($sl));
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

        return  [$data_compte , $montant , $rows]  ;
        
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


    public function save_matricules($path= "S:\Organisation&Informatique\Projets\jobs virements de masse\\fichiers valides\SNIM2.0\snimmatricule.xlsx"){
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
         $spreadsheet = $reader->load($path);
         $worksheet = $spreadsheet->getActiveSheet();
         $debutFile = false;
         foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $r = '';
            $i= 0;
             
            foreach ($cellIterator as $cell) {
                if($i == 0) $r .= $cell->getCalculatedValue();
                else $r .=';'.$cell->getCalculatedValue();
                $i++;
            }
            
            $ligne = '';
            $chaine = explode(';' , $r);
            //dd($chaine);
            if($debutFile == false &&  isset($chaine[0])  && trim($chaine[0]) === "Agence"){
                $debutFile = true;
                //dd($chaine[0]);
            }
            elseif($debutFile)    {
                //dd($debutFile);
                $matricule = new Matricule();
                $matricule->regles_salaire_id = 6;
                $matricule->matricule = trim($chaine[2]) ;
                $matricule->compte = trim($chaine[3]) ;
                $matricule->save();   
            }      
        }
        return "les matricules sont enrigistres";
    }

    private $requete = "select unique rpad('00013'||trim(a.age)||trim(a.ncp),26,' ') as dtcom, clc from prod.bkcom a where a.cfe='N' and a.ife='N' and a.ncp in (compte)";
    private $bkcom = "select unique a.age ,  a.ncp,  a.clc from prod.bkcom a where (a.cfe='N' and a.ife='N' and a.cpro in ('001','002','003','011','012','013','014','021','024') and dev='929') or ncp = '90000001467'" ;

   
   

   
}
