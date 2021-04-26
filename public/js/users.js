$('.select2').select2();

let table = $('#dataTableUsers').DataTable({
    "language": {
        url: "/js/i18n/dataTableLanguage.json",
    },
    ajax: {
        "url": Routing.generate('user_api'),
        "type": "POST"
    },
    columns: [
        {"data": 'Nom', 'title': 'Nom'},
        {"data": 'Prénom', 'title': 'Prénom'},
        {"data": 'Email', 'title': 'Email'},
        {"data": 'Statut', 'title': 'Statut'},
        {"data": 'Validateurs', 'title': 'Validateurs'},
        {"data": 'Dernière connexion', 'title': 'Dernière connexion'},
        {"data": 'cp', 'title': 'cp'},
        {"data": 'rtt', 'title': 'rtt'},
        {"data": 'Action', 'title': 'Action'},
    ]
});

function initialiserModalUser(path, modal) {
    let inputs = modal.find('.data');
    let Data = {};
    let missingInputs = [];
    inputs.each(function () {
        let val = $(this).val();
        let name = $(this).attr("name");
        Data[name] = val;
        // validation données obligatoires
        if ($(this).hasClass('needed')
            && (
                val === undefined
                || val === ''
                || val === null
                || (Array.isArray(val) && val.length === 0)
            )) {
            let label = $(this).closest('.form-group').find('label').text();
            // on enlève l'éventuelle * du nom du label
            label = label.replace(/\*/, '');
            missingInputs.push(label);
            $(this).addClass('is-invalid');
            $(this).next().find('.select2-selection').addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // ... et dans les checkboxes
    let checkboxes = modal.find('.checkbox');
    checkboxes.each(function () {
        Data[$(this).attr("name")] = $(this).is(':checked');
    });

    // si tout va bien on envoie la requête ajax...
    if (missingInputs.length == 0) {
        $.post(path, JSON.stringify(Data), function (data) {
            if (typeof data.errorMsg !== 'undefined') {
                $('.error-msg').html(data.errorMsg);
            } else if (data === 'USER_EDIT') {
                modal.find('.close').click();
                reloadDatatable($('#dataTableUsers'));
            } else {
                modal.find('.close').click();
                $('#buttonModalValidateNewUser').click();
                reloadDatatable($('#dataTableUsers'));
            }
        });
    } else {
        // ... sinon on construit les messages d'erreur
        let msg = '';

        // cas où il manque des champs obligatoires
        if (missingInputs.length > 0) {
            if (missingInputs.length == 1) {
                msg += 'Veuillez renseigner le champ ' + missingInputs[0] + ".<br>";
            } else {
                msg += 'Veuillez renseigner les champs : ' + missingInputs.join(', ') + ".<br>";
            }
        }
        modal.find('.error-msg').html(msg);
    }
}

function toggleStatus(id, div) {
    let status = id.is(':checked');
    if (status === false) {
        div.addClass('d-none');
    } else if (status === true) {
        div.removeClass('d-none');
    }
}

function deleteUser(value) {
    let path = Routing.generate('delete_user');
    $.post(path, value, function () {
        table.ajax.reload();
    });
}
