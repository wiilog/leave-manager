function clearModal(modal) {
    let $modal = $(modal);
    let inputs = $modal.find('.modal-body').find("input");
    // on vide tous les inputs (sauf les disabled et les input hidden)
    inputs.each(function () {
        if ($(this).attr('disabled') !== 'disabled' && $(this).attr('type') !== 'hidden') {
            $(this).val("");
        }
        // on enlève les classes is-invalid
        $(this).removeClass('emptyField');
    });
    // on vide tous les select2
    let selects = $modal.find('.modal-body').find('.select2');
    selects.each(function () {
        $(this).val($(this).data("default")).trigger('change');
    });
    // on vide les messages d'erreur
    $modal.find('.error-msg').html('');
    // on vide les div identifiées comme à vider
    $modal.find('.clear').html('')
}

function deleteRow(button, modal, submit) {
    let id = button.data('id');
    modal.find(submit).attr('value', id);
}

function editRow(button, path, modal, submit){
    modal.find('.error-msg').html('');
    let id = button.data('id');
    modal.find(submit).attr('value', id);
    $.post(path, JSON.stringify(id), function(data){
        modal.find('.container-fluid').html(data.html);
        modal.find('#validatorsEdit').val(data.listValidateur).select2();
    }, 'json');
}

function reloadDatatable(table){
    table.DataTable().ajax.reload(function (json) {
        if (this.responseText !== undefined) {
            $('#myInput').val(json.lastInput);
        }
    });
}

