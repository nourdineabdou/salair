@extends('layouts.app')
@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4">Listes des intégrations </h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item active"> pour la date {{$date}} </li>
        </ol>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Liste
        </div>
        <div class="card-body">
        <table  class="table">
            <thead>
                <tr>
                    <th>Institution</th>
                    <th>Utilisateur</th>
                    <th>Heure</th>
                    <th>Fichier</th>
                </tr>
            </thead>
            <tfoot>
                @foreach($historiques as $historique)
                    <tr>
                        <td>{{$historique->reglesSalaire->nom}}</td>
                        <td>{{$historique->user->name}}</td>
                        <td>{{ $historique->created_at->format('H:i:s') }}</td>
                        <td> <a href="#" target="_blank"  ><i class="fa fa-file-excel-o"></i></a> </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
@endsection