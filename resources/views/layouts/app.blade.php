<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Integration des salaires</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <script type="text/javascript">
            var racine = '{{url("/")}}/';
            var msg_chargement = '{{ trans("message_erreur.chargement") }}';
            var erreur_req = "{{ trans('message_erreur.request_error') }}";
            var erreur_validation = "{{ trans('message_erreur.validate_error') }}";
            var champs_obigatoire_st = "{{ trans("message_erreur.champs_obligatoire_st") }}";
            var prametre_st = "{{ trans("message_erreur.prametre_st") }}";
            var msg_erreur = "{{ trans("message_erreur.msg_erreur") }}";
            var origine = "{{ trans("text_archive.origine") }}";
            var destination = "{{ trans("text_archive.destination") }}";
            var lang = '{{app()->getLocale()}}';
        </script>
    </head>
    <body class="sb-nav-fixed">
           
            <!-- Sidebar --> 
                @include('layouts.navigation.navbar')
            <!-- End of Sidebar -->
            
   
        <div id="layoutSidenav">
                <!-- Sidebar --> 
                    @include('layouts.navigation.sidebar')
                <!-- End of Sidebar -->
                <div id="layoutSidenav_content">
                    <main>
                        @yield('content')        
                    </main>
                    <footer class="py-4 bg-light mt-auto">
                            <div class="container-fluid px-4">
                                <div class="d-flex align-items-center justify-content-between small">
                                    <div class="text-muted">Copyright &copy; WEB SITE 2025</div>
                                    <div>
                                        <a href="#">Privacy Policy</a>
                                        &middot;
                                        <a href="#">Terms &amp; Conditions</a>
                                    </div>
                                </div>
                            </div>
                    </footer>
                </div>
        </div>
        @foreach (['main','second','third','forth','add','de_tab','de'] as $type_modal)
            <di v id="{{$type_modal}}-modal" class="modal fade" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header-body">
                        </div>
                    </div>
                </div>
            </div>
        @endforeach 
        <script src="js/jquery.js"></script>    
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
        <script src="js/init.js"></script>
    </body>
</html>