// test
$(document).ready(function () {
    // racine = '/onispa/public/';
    // Ajout du CSRF Token pour les requettes ajax

    // $(document).ready(function() {
    //     setTimeout(function() {
    //         alert(15)
    //     }, 5000);
    // })
    //getfile();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        statusCode: {
            401: function(jqxhr, textStatus, errorThrown) {
                alert("Vous devez se connecter")
                window.location = racine+"login";
            }
        }

    });
    // Customzing dataTable ajax errors
    $.fn.dataTable.ext.errMode = function (settings, helpPage, message) {
        console.log(message);
        $.alert("Une erreur est survenue lors du chargement du contenu veuillez réessayer ou actualiser la page!");
    };

    loading_content = '<div class="d-flex justify-content-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>';

    resetInit();

});


// Ouvrir dans le Main Modal
function openInModal(link, aftersave = null, modal = "main" , largeModal = "sm") {
    $.ajax({
        type: 'get',
        url: link,
        success: function (data) {
            container = "#" + modal + '-modal';
            $(container + " .modal-dialog").addClass("modal-lg");
           // $("#" + modal + " .modal-dialog").addClass("modal-" + largeModal);
            $("#" + modal + "-modal .modal-header-body").html(data);
            $("#" + modal + "-modal").modal('show');
           // resetInit();
            if (aftersave)
                aftersave();
        },
        error: function () {
            $.alert(msg_erreur);
        }
    });
}


// 

function refresh(link , link2){
    getTheContent('refrech/delta' , "#tbody-salut")
    window.open(link2,"_blank")
}
// Get the content from Ajax and show it in a div
function getTheContent(link, container) {
    let loading_content = '<div class="d-flex justify-content-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>';

    $(container).html(loading_content);
    $.ajax({
        type: 'get',
        url: racine + link,
        success: function (data) {
            $(container).html(data);
            resetInit();
        },
        error: function () {
            $.alert(msg_erreur);
        }
    });
}

// Init of DataTables
function setDataTable(element) {
    if (!$.fn.dataTable.isDataTable(element) && $(element).length) {
        var colonnes = [];
        var index = [];
        var target;
        var ordre;
        var search;
        var visibility =[];
        var showbtn=[];
        var paginate=true;
        var infoPaginate=true;

        if (typeof $(element).attr("export") !== 'undefined') {

            showbtn=['colvis', 'csv', 'excel', 'pdf', 'print'];
        }
        if (typeof $(element).attr("index") !== 'undefined') {
            var lists = $(element).attr("index").split(',');
            for (var i = 0; i < lists.length; i++) {
                index.push(parseInt(lists[i]));
            }
        } else {
            index.push(-1);
        }
        if (typeof $(element).attr("hiddens") !== 'undefined') {
            var lists = $(element).attr("hiddens").split(',');
            for (var i = 0; i < lists.length; i++) {
                visibility.push(parseInt(lists[i]));
            }
        }
        var nbr = $(element).attr("nbr");
        if (typeof $(element).attr("nbr") !== 'undefined') {
            nbr = $(element).attr("nbr");
        } else {
            nbr = 10;
        }
        if (typeof $(element).attr("ordre") !== 'undefined') {
            ordre = $(element).attr("ordre");
        }
        else
            ordre='asc';
        if (typeof $(element).attr("search") !== 'undefined') {
            search = false;
        } else {
            search = true;
        }
        if (typeof $(element).attr("disblePanginate") !== 'undefined') {
            paginate = false;
            infoPaginate =false;
        }
        var lists = $(element).attr("colonnes").split(',');
        for (var i = 0; i < lists.length; i++) {
            colonnes.push({
                'data': lists[i],
                'name': lists[i]
            });
        }
        target = 'targets:' + index;
        oTable = $(element).DataTable({
            oLanguage: {
                sUrl: racine + "vendor/datatables/datatable-"+lang+".json",
            },
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "orderCellsTop": true,
            "bDestroy": true,
            "cache": false,
            "searching": search,
            "pageLength": nbr,
            "iDisplayLength": nbr,
            //"ordering": false,
            "bPaginate": paginate,
            "bInfo" : infoPaginate,
            "order": [[ 0, ordre ]],
            //"aoColumnDefs": [{ "bVisible": false, "aTargets": visibility }],

            "columnDefs": [{
                orderable: false,
                targets: index
            },
                {
                    searchable: false,
                    targets: index
                },
                {
                    visible:false,
                    targets: visibility
                }
            ],
            "ajax": $(element).attr("link"),
            "columns": colonnes,
            "drawCallback": function () {
                // init tooltips
                // $('[data-toggle="tooltip"]').tooltip();
                resetInit();
            },
            //dom: 'Blfrtip',

            buttons:
            showbtn

        })
    }
}

function openObjectModal(id, lemodule, datatableshow = "#datatableshow", modal = "main", tab = 1, largeModal = 'lg') {
    $.ajax({
        type: 'get',
        url: racine + lemodule + '/get/' + id,
        success: function (data) {
            container = "#" + modal + '-modal';
            $(container + " .modal-dialog").addClass("modal-" + largeModal);
            $(container + " .modal-header-body").html(data);
            $(container).modal();
            setMainTabs(tab, container);
            $(datatableshow).DataTable().ajax.url($(datatableshow).attr('link') + "/" + id).load();
            resetInit();
        },
        error: function () {
            $.alert(msg_erreur);
        }
    });
    return false;
}

function openFormAddInModal(lemodule, id = false, static = true) {
    if (id != false)
        url = racine + lemodule + '/add/' + id;
    else
        url = racine + lemodule + '/add/';
    $.ajax({
        type: 'get',
        url: url,
        success: function (data) {
            $("#add-modal .modal-dialog").addClass("modal-lg");
            $("#add-modal .modal-header-body").html(data);
            if(static)
                $("#add-modal").modal({
                    backdrop: 'static',
                    keyboard: false
                });
            else
                $("#add-modal").modal();
            resetInit();
        },
        error: function () {
            $.alert(msg_erreur);
        }
    });
}

function setMainTabs(tab = 1, container = '') {
    $(container + " .main-tabs a[data-toggle='tab']").on('click',function(){
        link =$($(this)).attr("link");
        href = $($(this)).attr("href");
        id = $($(this)).attr("id");
        getTheContent(link, container + ' ' + href);
    });
    $(container + ' #link' + tab).trigger('click');
}

function setMainCollaps(tab = 1, container = '') {

    $(container + " .main-collapse a[data-toggle='collapse']").on('click',function(){
        link =$($(this)).attr("link");
        href = $($(this)).attr("href");
        id = $($(this)).attr("id");
        getTheContent(link, container + ' ' + href);
    });

    $(container + ' #link' + tab).trigger('click');
}

// enregister plusieurs formulaires
function saveform_all(element, aftersave = null) {
    var containers = $(element).attr('container');
    var froms = containers.split(',');
    $.each( froms, function( index, value ) {
        saveform(element,null,value)
    });
}

function addObject(element, lemodule, datatable = "#datatableshow", modal = "add-main", tab = 1, largeModal = 'lg') {
    saveform(element, function (id) {
        $(datatable).DataTable().ajax.reload();
        $(element).attr('disabled', 'disabled');
        var modals = modal.split('-');
        var add = modals[0];
        var main = modals[1];
        setTimeout(function () {
            $('#'+add+'-modal').modal('toggle');
            openObjectModal(id, lemodule, datatable, main, tab, largeModal);
        }, 1500);
    });
}
function addObjectForm(element, lemodule, datatable = "#datatableshow",container_result='#result')
{
    saveform(element, function (id) {
        setTimeout(function () {
            $(container_result).html('');
            $('.btn-add').show();
            if(datatable != false)
                $(datatable).DataTable().ajax.url($(datatable).attr('link') + "/" + id).load();
        }, 1500);
    });
}

function saveformAndRefreshDT(element, datatable = "#datatableshow") {
    saveform(element, aftersave, function () {
        $(datatableshow).DataTable().ajax.reload();
    });
}
/*function saveform(element, aftersave = null) {
    var container = $(element).attr('container');
    alert(container)
    $('#' + container + ' #form-errors').hide();
    $(element).attr('disabled', 'disabled');
    $('#' + container + ' .main-icon').hide();
    $('#' + container + ' .spinner-border').show();
    $.ajax({
        type: $('#' + container + ' form').attr("method"),
        url: $('#' + container + ' form').attr("action"),
        data: new FormData($('#' + container + ' form')[0]),
        cache: false,
        contentType: false,
        processData: false,
        // dataType: 'json',
        success: function (data) {
            console.log(data);
            $('#' + container + ' .spinner-border').hide();
            $('#' + container + ' .answers-well-saved').show();
            $(element).removeAttr('disabled');
            setTimeout(function () {
                $('#' + container + ' .answers-well-saved').hide();
                $('#' + container + ' .main-icon').show();
            }, 3500);
            if (aftersave) {
                aftersave(data);
            }
        },
        error: function (data) {
            if (data.status === 422) {
                var errors = data.responseJSON;
                // errorsHtml = '<ul class="list-group">';
                var erreurs = (errors.errors) ? errors.errors : errors;
                // console.log(erreurs)
                $.each(erreurs, function (key, value) {
                    var input = $(`input[name=${key}]`);
                    console.log(key)
                    // checking if input in errors object
                    if (input.attr('name') === key) {
                        input.addClass('is-invalid');
                        input.next('.invalid-feedback').remove();
                        $(`<p class='invalid-feedback'>${value}</p>`).insertAfter(input);
                    }
                    // errorsHtml += '<li class="list-group-item list-group-item-danger">' + value[0] + '</li>';
                });

                //   removing error class if input not empty
                $.each($('#' + container + ' form input'), function (k, item) {
                    var input = $(item);
                    // alert(123)
                    if (!(input.attr('name') in erreurs)){
                        input.next('p .invalid-feedback').remove();
                        input.removeClass('is-invalid');
                    }
                });

                /!*$.each(erreurs, function (key, value) {
                    errorsHtml += '<li class="list-group-item list-group-item-danger">' + value[0] + '</li>';
                });
                errorsHtml += '</ul>';
                $('#' + container + ' #form-errors').show().html(errorsHtml);*!/
            }
            else if (data.status === 412){
                alert('error 412')
            }
            else {
                alert(msg_erreur);
            }
            $('#' + container + ' .spinner-border').hide();
            $('#' + container + ' .main-icon').show();
            $(element).removeAttr('disabled');
        }
    });
}*/

function showChildren(id, placeholder) {
    let placeholderE = $('#' + placeholder )
    placeholderE.html('<h1>this is a text</h1>')
}

/*function saveform(element, aftersave = null) {
    var container = $(element).attr('container');
    $('#' + container + ' #form-errors').hide();
    $(element).attr('disabled', 'disabled');
    $('#' + container + ' .main-icon').hide();
    $('#' + container + ' .spinner-border').show();
    $.ajax({
        type: $('#' + container + ' form').attr("method"),
        url: $('#' + container + ' form').attr("action"),
        data: new FormData($('#' + container + ' form')[0]),
        cache: false,
        contentType: false,
        processData: false,
        // dataType: 'json',
        success: function (data) {
            console.log(data);
            $('#' + container + ' .spinner-border').hide();
            $('#' + container + ' .answers-well-saved').show();
            $(element).removeAttr('disabled');
            setTimeout(function () {
                $('#' + container + ' .answers-well-saved').hide();
                $('#' + container + ' .main-icon').show();
            }, 3500);
            if (aftersave) {
                aftersave(data);
            }
            $('#' + container + ' .is-invalid').each(function(index,item){
                $(item).removeClass('is-invalid');
            });
            $('#' + container + ' .invalid-feedback').each(function(index,item){
                $(item).remove();
            });
        },
        error: function (data) {
            if (data.status === 422) {
                var errors = data.responseJSON;
                errorsHtml = '<ul class="list-group">';
                var erreurs = (errors.errors) ? errors.errors : errors;
                $.each(erreurs, function (key, value) {
                    errorsHtml += '<li class="list-group-item list-group-item-danger">' + value[0] + '</li>';
                });
                errorsHtml += '</ul>';
                $('#' + container + ' #form-errors').show().html(errorsHtml);
                $.each(erreurs, function (key, value) {
                    var input = $(`.form-control[name=${key}]`);
                    console.log(key)
                    // checking if input in errors object
                    if (input.attr('name') === key) {
                        input.addClass('is-invalid');
                        input.next('.invalid-feedback').remove();
                        $(`<p class='invalid-feedback'>${value}</p>`).insertAfter(input);
                    }
                    // errorsHtml += '<li class="list-group-item list-group-item-danger">' + value[0] + '</li>';
                });
                //   removing error class if input not empty
                $.each($('#' + container + ' form input'), function (k, item) {
                    var input = $(item);
                    // alert(123)
                    if (!(input.attr('name') in erreurs)){
                        input.next('p .invalid-feedback').remove();
                        input.removeClass('is-invalid');
                    }
                });

            } else {
                alert(msg_erreur);
            }
            $('#' + container + ' .spinner-border').hide();
            $('#' + container + ' .main-icon').show();
            $(element).removeAttr('disabled');
        }
    });
}*/
function saveform(element, aftersave = null) {
    var container = $(element).attr('container');
    $('#' + container + ' #form-errors').hide();
    $(element).attr('disabled', 'disabled');
    $('#' + container + ' .main-icon').hide();
    $('#' + container + ' .spinner-border').show();
    $.ajax({
        type: $('#' + container + ' form').attr("method"),
        url: $('#' + container + ' form').attr("action"),
        data: new FormData($('#' + container + ' form')[0]),
        cache: false,
        contentType: false,
        processData: false,
        // dataType: 'json',
        success: function (data) {
            console.log(data);
            errorsHtml = '<ul class="list-group">';
            errorsHtml += '<li class="list-group-item list-group-item-success"> Montant total '+(data.montant/100).toFixed(2)+'</li>';
            $('#' + container + ' .spinner-border').hide();
            $('#' + container + ' .answers-well-saved').show();
            $(element).removeAttr('disabled');
            setTimeout(function () {
                $('#' + container + ' .answers-well-saved').hide();
                $('#' + container + ' .main-icon').show();
            }, 3500);
            console.log()
            if( typeof data === 'object' && ('status' in data) && data.status===false )
            {
                errorsHtml += '<li class="list-group-item list-group-item-danger">Listes des comptes non actifs</li>';
                $.each(data.response, function ( key , value) {
                    errorsHtml += '<li class="list-group-item list-group-item-danger">' + value + '</li>';
                });
                $('#' + container + ' #form-errors').show().html(errorsHtml);
                
            }
            else {
                console.log('entrer')
                $("#block-success").html(data);

            }
            errorsHtml += '</ul>';  
            $('#' + container + ' .is-invalid').each(function(index,item){ $(item).removeClass('is-invalid'); });
            $('#' + container + ' .invalid-feedback').each(function(index,item){ $(item).remove(); });
            //window.location.href = data;
        },
        error: function (data) {
            if (data.status === 422) {
                var errors = data.responseJSON;
                errorsHtml = '<ul class="list-group">';
                var erreurs = (errors.errors) ? errors.errors : errors;
                $.each(erreurs, function ( key , value) {
                    errorsHtml += '<li class="list-group-item list-group-item-danger">' + value[0] + '</li>';
                });
                errorsHtml += '</ul>';
                $('#' + container + ' #form-errors').show().html(errorsHtml);
                $.each(erreurs, function (key, value) {
                    var input = $(`.form-control[name=${key}]`);
                    // console.log(key)
                    // checking if input in errors object
                    if (input.attr('name') === key) {
                        input.addClass('is-invalid');
                        input.next('.invalid-feedback').remove();
                        $(`<p class='invalid-feedback'>${value}</p>`).insertAfter(input);
                    }
                    // errorsHtml += '<li class="list-group-item list-group-item-danger">' + value[0] + '</li>';
                });
                //   removing error class if input not empty
                $.each($('#' + container + ' form input'), function (k, item) {
                    var input = $(item);
                    // alert(123)
                    if (!(input.attr('name') in erreurs)){
                        input.next('p .invalid-feedback').remove();
                        input.removeClass('is-invalid');
                    }
                });

            } else {
                alert(msg_erreur);
            }
            $('#' + container + ' .spinner-border').hide();
            $('#' + container + ' .main-icon').show();
            $(element).removeAttr('disabled');
        }
    });
}

function confirmAction(link, text, aftersave = null) {
    $.confirm({
        title: 'Confirmation',
        content: text,
        buttons: {
            confirm: function () {
                $.ajax({
                    type: 'GET',
                    url: link,
                    success: function (data) {
                        if (data.success == "true") {
                            $.dialog(data.msg, 'Confirmation');
                            if (aftersave) {
                                aftersave(data);
                            }
                        }
                        else
                            $.dialog(data.msg, 'Erreur');
                    },
                    error: function () {
                        $.dialog(msg_erreur);
                    }
                });
            },
            close: function () {
            }
        }
 });
}

function ConfirmAndRefreshDT(link, text, datatableshow = "#datatableshow") {
    confirmAction(link, text, function () {
        $(datatableshow).DataTable().ajax.reload();
    });
}

function deleteObject(link, text, datatableshow = "#datatableshow") {
    ConfirmAndRefreshDT(link, text, datatableshow);
}

// updating a group des elements
function updateGroupeElements(element = null) {
    // $('[data-toggle="tooltip"]').tooltip('dispose');
    $('[data-toggle="tooltip"]').removeClass('ui-tooltip');
    var questions = $(".group-elements").sortable('toArray');
    var childscount = $(".group-elements li").length;
    var idgroup = $(".group-elements").attr('idgroup');
    var datatble = $(".group-elements").attr('datatable-source');
    var lien = $(".group-elements").attr('lien');
    if (element) {
        if ($(element).hasClass("close")) {
            questions = jQuery.grep(questions, function (value) {
                return value != $(element).parent().attr('id');
            });
            $(element).html('<i style="font-size:13px" class="fa fa-refresh fa-spin fa-fw"></i>');
        } else {
            $(element).children('i').removeClass('fa-arrow-right').addClass('fa-refresh fa-spin');
            questions.push($(element).attr('idelt'));
        }
    }
    if (questions.length)
        var qsts = questions.join();
    else
        var qsts = 0;
    var link = racine + lien + "/" + qsts + '/' + idgroup;
    $.ajax({
        type: 'GET',
        url: link,
        success: function (data) {
            if (element) {
                if ($(element).hasClass("close"))
                    $(element).parent().remove();
                else {
                    var idelt = $(element).attr('idelt');
                    var libelle = $(element).attr('libelle');
                    $(element).parents('tr').remove();
                    $(".group-elements").append('<li class="list-group-item" id="' + idelt + '">' + libelle + '<button type="button" idelt="' + idelt + '" class="close" aria-hidden="true" onclick="updateGroupeElements(this)">&times;</button></li>');
                }
                if ($('.btn-drftval').length) {
                    if (qsts.length > 0)
                        $('.btn-drftval').show();
                    else
                        $('.btn-drftval').hide();
                }
            }
            $(datatble).DataTable().ajax.reload();
        },
        error: function () {
            if (element) {
                if ($(element).hasClass("close"))
                    $(element).html('&times;');
                else {
                    $(element).children('i').removeClass('fa-refresh fa-spin').addClass('fa-arrow-right');
                }
            }
            $.alert(msg_erreur);
        }
    });
}

function resetInit() {
    if ( $.isFunction(window.resetInitModule) ) {
        resetInitModule();
    }
    //$(":input").inputmask();
    // $("#img_profile").fileinput({
    //     language: "fr",
    //     'showUpload': false,
    //     // uploadUrl: "/site/image-upload",
    //     uploadUrl: "/file-upload-batch/2",
    //     allowedFileExtensions: ["jpg", "png", "gif"],
    //     maxImageWidth: 20,
    //     maxImageHeight: 10,
    //     resizePreference: 'height',
    //     maxFileCount: 1,
    //     resizeImage: true
    // });
  
    $(".collapse").on('show.bs.collapse', function(){
        
        $(this).prev(".card-header").find(".fa").removeClass("fa-plus").addClass("fa-minus");
    }).on('hide.bs.collapse', function(){
        
        $(this).prev(".card-header").find(".fa").removeClass("fa-minus").addClass("fa-plus");
    });
    // init du select picker
    $('.selectpicker').selectpicker({
        size: 10,
        noneSelectedText: 'Rien sélectionné',
        selectAllText: 'sélectionner tous',
        deselectAllText: 'désélectionner tous',
    });
    // Basic Select2 select
    $(".select2").select2({
        // the following code is used to disable x-scrollbar when click in select input and
        // take 100% width in responsive also
        // dropdownAutoWidth: true,
        width: '100%'
    });
    $(".select2-tag").select2({
        // the following code is used to disable x-scrollbar when click in select input and
        // take 100% width in responsive also
        // dropdownAutoWidth: true,
        width: '100%',
        DropdownAutoWidth:true,
        tags:true
    });
    //Grouping
    $(".group-elements").sortable({
        axis: 'y',
        update: function (event, ui) {
            updateGroupeElements();
        }
    });

    // Datatables to load General
    if ($('#datatableshow').length) setDataTable('#datatableshow');
    if ($('#datatableshow_ind').length) setDataTable('#datatableshow_ind');
    if ($('#datatableshow_ged').length) setDataTable('#datatableshow_ged');
    if ($('.datatableshow').length) setDataTable('.datatableshow');
    // Datatables des onglets
    for (let i = 1; i < 6; i++) {
        if ($('#datatableshow' + i).length) setDataTable('#datatableshow' + i);
        if ($('.datatableshow' + i).length) setDataTable('.datatableshow' + i);
    }

    // init tooltips
    $('[data-toggle="tooltip"]').tooltip();

}

// A revoir (il faut utiliser saveformAndRefreshDT)
$('#main-modal').on('hidden.bs.modal', function () {
    if ($('.datatableshow').length) {
        $('.datatableshow').DataTable().ajax.reload();
    } else if ($('#datatableshow').length) {
        $('#datatableshow').DataTable().ajax.reload();
    }
});

(function($, window) {
    'use strict';
    var MultiModal = function(element) {
        this.$element = $(element);
        this.modalCount = 0;
    };
    MultiModal.BASE_ZINDEX = 1040;
    MultiModal.prototype.show = function(target) {
        var that = this;
        var $target = $(target);
        var modalIndex = that.modalCount++;
        $target.css('z-index', MultiModal.BASE_ZINDEX + (modalIndex * 20) + 10);
        window.setTimeout(function() {
            if(modalIndex > 0)
                $('.modal-backdrop').not(':first').addClass('hidden');

            that.adjustBackdrop();
        });
    };
    MultiModal.prototype.hidden = function(target) {
        this.modalCount--;
        if(this.modalCount) {
            this.adjustBackdrop();
            $('body').addClass('modal-open');
        }
    };
    MultiModal.prototype.adjustBackdrop = function() {
        var modalIndex = this.modalCount - 1;
        $('.modal-backdrop:first').css('z-index', MultiModal.BASE_ZINDEX + (modalIndex * 20));
    };
    function Plugin(method, target) {
        return this.each(function() {
            var $this = $(this);
            var data = $this.data('multi-modal-plugin');

            if(!data)
                $this.data('multi-modal-plugin', (data = new MultiModal(this)));

            if(method)
                data[method](target);
        });
    }
    $.fn.multiModal = Plugin;
    $.fn.multiModal.Constructor = MultiModal;
    $(document).on('show.bs.modal', function(e) {
        $(document).multiModal('show', e.target);
    });
    $(document).on('hidden.bs.modal', function(e) {
        $(document).multiModal('hidden', e.target);
    });
}(jQuery, window));
