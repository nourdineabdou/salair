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
use SplFileObject;
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

    public function snimBanque($path , $regle){
        $lines = $this->read_text($path);
        $error = false;
        $data_compte = collect();
        $pos = [22,10];
        if($regle->postions) $pos =  explode("," ,$regle->postions);
        $resultat = $this->get_montant_txt($path , $pos[0] , $pos[1] );
        $montant = $resultat[0]; 
        $rows = $resultat[1];
        //$regle = ReglesSalaire::find(6);
        $espace = explode(',',$regle->nbespace);
        $ordre = explode(',',$regle->ordre);
        $path_dest = public_path("move/".$regle->name_file. '.txt');
        //$path_dest = "C:\Users\848295\Desktop\\exel_file\\".$regle->name_file. '.txt';
        
        if($lines == null)
            return  "le fichier selectionnne ne contient pas d'information";
        else{
            $mont = trim($regle->cle_entete).$this->get_0($montant); 
            $tete =  str_replace("montant", $mont, $regle->entete) ;
            $tete =  str_replace("date", Carbon::now()->format('dmy'), $tete) ;
            File::put($path_dest, $tete."\n");
            $detaillCompte = null;
            foreach ($lines as $key => $ligne) {
                    $ecrire = "";
                    $ligne = $this->suprimerBOM($ligne);
                    $matricule =   substr($ligne, 0, 8);
                    $compte = Matricule::where('matricule',trim($matricule))->first();      
                        if($compte && !$error){
                            $detaillCompte = Compte::where( 'ncp'  , $compte->compte)->first();
                            $dtcomp = '00013'.$detaillCompte->age.$detaillCompte->ncp;
                            $ecrire ='2'.$this->get_sapce( (int) $espace[0] -strlen("2") );
                            $ecrire .="A".$this->get_sapce( (int) $espace[1] - strlen("A"));
                            $ecrire .='ABM'.$this->get_sapce( (int) $espace[2]- strlen('ABM'));
                            $ecrire .=trim($dtcomp).$this->get_sapce($espace[3]- strlen(trim($dtcomp)));
                            $sl = trim($detaillCompte->clc).$this->get_0( (int) substr($ligne , $pos[0] , $pos[1]) ).'VIREMENT';
                            $ecrire .=$sl.$this->get_sapce( (int) $espace[4]- strlen($sl));
                            $ecrire .=$regle->nom_virement;
                            File::append($path_dest, $ecrire."\n");
                        }
                        else{
                            $error = true;
                            if(!$detaillCompte){
                                $data_compte->push($compte ? $compte->compte : $matricule);
                            }
                        }
            }
        }
        //dd($data_compte);
        return  [$data_compte , $montant , $rows]  ;
    }

    // public function get_montant($ligne){
    //     $tab = substr($ligne , 22 , 11);
    // }

    public function store(Request $request)
    {
        $request->validate([
            'fichier' => 'required',
        ],
         ["fichier.required"=>"Assurez-vous de spécifier un fichier"]);
        //$path_dest = "C:\Users\Administrateur\Desktop\\veth\mauri.txt"; 
        $regle = ReglesSalaire::find($request->id);
       if($regle->extension == 'xlsx')
            $response=   $this->salaire_rim($request->file('fichier')->path() , $regle);
        else{
            //dd(1);
            $response=   $this->snimBanque($request->file('fichier')->path() , $regle);

        }

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


    public function read_text($path = "S:\Organisation&Informatique\Projets\jobs virements de masse\\fichiers valides\SNIM2.0\snimsalaire.txt"){
            
           // Crée un objet SplFileObject pour le fichier spécifié
            $file = new SplFileObject($path);
            // Active la lecture ligne par ligne
            $file->setFlags(SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY);
            return $file;
        
    }

    public function suprimerBOM($line){
        if (substr($line, 0, 3) === "\xEF\xBB\xBF")
            // Si le BOM est présent, on le supprime
               $phraseSansBOM = substr($line, 3); // Supprime les 3 premiers caractères (BOM)
       else 
           $phraseSansBOM = $line;
       return $phraseSansBOM;

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

    // extraire le montant pour un fichier txt

    public function  get_montant_txt($path , $pos = 22 , $nb = 10){
        $file = new SplFileObject($path);
        // Active la lecture ligne par ligne
        $file->setFlags(SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY);
        //return $file;
          $montant = 0;
          $nb = 0;
        foreach ($file as $key => $ligne) {
            $ligne = $this->suprimerBOM($ligne);
            $montant += (double) substr($ligne , $pos , $nb);
            $nb++;
        }
        return [$montant , $nb];
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
                $detaillCompte = null;
                if(Matricule::where('regles_salaire_id' ,$regle->id )->first() ){
                   $matricule =  Matricule::where('matricule' , trim($chaine[$regle->matricule]))->first();
                   if($matricule) $detaillCompte = Compte::where( 'ncp'  ,$matricule->compte)->first();
                    else  $error= true; 
                }else {
                    $detaillCompte = Compte::where( 'ncp'  , $chaine[$regle->compte])->first();
                }
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
                    if(Matricule::where('regles_salaire_id' ,$regle->id )->first() && !$matricule){
                        $data_compte->push(trim($chaine[$regle->matricule]));
                    }
                    elseif(!$detaillCompte){
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


    public function save_matricules($path= "S:\Organisation&Informatique\Projets\jobs virements de masse\\fichiers valides\FONCTION PUBLIQUE2.0 - Copie\\fpmatricule.xlsx"){
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
                $matricule = new Matricule();
                $matricule->regles_salaire_id = 10;
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
