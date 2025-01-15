<ul class="list-group">

<li class="list-group-item list-group-item-danger">Les Comptes non active</li>

@foreach($errors as $error)
<li class="list-group-item list-group-item-danger">{{$error}}</li>

@endforeach

<ul>