@foreach( $histo_this_days as $histo )
    <tr>
        <th scope="col">{{$histo->reglesSalaire->nom}}</th>
        <th scope="col">{{$histo->created_at->format('H:i:s')}}</th>
        <th scope="col">{{$histo->nbres_lignes}}</th>
        <th scope="col">{{$histo->montant_Total}}</th>
        <th scope="col">{{$histo->user->name}}</th>
        <th scope="col">{{'Traité'}}</th>
        <th scope="col"><a href="#" target="_blank"  ><i class="fa fa-file-excel-o"></i></a></th>
    </tr>
@endforeach