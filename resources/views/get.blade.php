<x-modal.modal-header-body>
    <x-slot name="title">Integration salair {{$regle->nom}} </x-slot>
    <x-card>
        <div class="row">
            <div class="col-md-12" id="execution">
                <form class="" action="{{url('chercher')}}" method="post">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col">
                            <x-forms.input
                                label="Chercher"
                                class="required"
                                name="fichier"
                                type="file"
                                placeholder="fichier">
                            </x-forms.input>
                            <input Type="hidden" name="id" value="{{$regle->id}}" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="text-left mt-1">
                                <x-buttons.btn-save
                                    onclick="saveform(this ,  function(data){
                                       
                                       window.open(racine+'download/'+data , '_blank');
                                       //alert(racine+'download/'+data)
                                       } 
                                       )"
                                    container="execution">
                                    Exécuter 
                                </x-buttons.btn-save>
                                <div id="form-errors" class="text-left"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </x-card>
    <div class="row mt-1">
        <div class="col-md-8" id="block-success">
            
        </div>
    </div>
</x-modal.modal-header-body>