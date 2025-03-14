<div class="card" >
  <div class="card-body">
    <h5 class="card-title">Traitement finalisé</h5>
    <h6 class="card-subtitle mb-2 text-muted">Montant total : {{ number_format($montant, 2, ',', ' ')}}</h6>
    <p class="card-text">
        Nombres de lignes à intégrer : {{$lignes}} 
    </p>
    <a onclick=" refresh('{{route('refrech')}}' , '{{$route}}')"  href="#"  class="card-link">Valider</a>
    <a href="" class="card-link">Quitter</a>
  </div>
</div>