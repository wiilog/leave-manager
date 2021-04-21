let pathDataTable = Routing.generate('print_data_table');

$('.select2').select2();

$('#demandeurFilter').select2({
    ajax: {
        url: Routing.generate('get_demandeur'),
        dataType: 'json',
        delay: 250,
    },
    language: {
        inputTooShort: function () {
            return 'Veuillez entrer au moins 1 caractère.';
        },
        searching: function () {
            return 'Recherche en cours...';
        }
    },
    placeholder: 'Demandeur',
    allowClear: true,
    minimumInputLength: 1,
});

$('#validateurFilter').select2({
    ajax: {
        url: Routing.generate('get_demandeur'),
        dataType: 'json',
        delay: 250,
    },
    language: {
        inputTooShort: function () {
            return 'Veuillez entrer au moins 1 caractère.';
        },
        searching: function () {
            return 'Recherche en cours...';
        }
    },
    placeholder: 'Validateur',
    allowClear: true,
    minimumInputLength: 1,
});

let dataTableHoliday = $('#dataTableHoliday').DataTable({
    "language": {
        url: "/js/i18n/dataTableLanguage.json",
    },
    ajax: {
        "url": pathDataTable,
        "type": "POST"
    },
    columns: [
        {"data": 'Demandeur', 'title': 'Demandeur', 'name': 'Demandeur'},
        {"data": 'Date de demande', 'title': 'Date de demande', 'name': 'Date de demande'},
        {"data": 'Description', 'title': 'Description', 'name': 'Description'},
        {"data": 'Début', 'title': 'Début', 'name': 'Début'},
        {"data": 'Fin', 'title': 'Fin', 'name': 'Fin'},
        {"data": 'Nombre jours', 'title': 'Nombre jours', 'name': 'Nombre jours'},
        {"data": 'Statut', 'title': 'Statut', 'name': 'Statut'},
        {"data": 'Date de validation', 'title': 'Date de validation', 'name': 'Date de validation'},
        {"data": 'Validateur', 'title': 'Validateur', 'name': 'Validateur'},
        {"data": 'Action', 'title': 'Action', 'name': 'Action'},
    ],
});

initiRadioButtonDayUpdate(true);

function addHoliday(idUser) {
    let path = Routing.generate('add_holiday');
    let $modalAdd = $('#modalAddHoliday');
    let valideBtn = $modalAdd.find('.btn_valide');
    let params = {
        id: idUser
    };

    addOrEditHoliday($modalAdd, path, valideBtn, params);
}

function deleteHoliday(elem) {
    let pathDelete = Routing.generate('delete_holiday');
    let sure = confirm("Êtes-vous sur de vouloir supprimer ces vacances ?");

    if (sure) {
        let param = {
            id: $(elem).data("id"),
        };
        $.post(pathDelete, JSON.stringify(param), function (data) {
            updateCurrentUserHolidays(data);
            $(elem).closest('tr').remove();
        }, 'json');
    }
}

function printInfoHoliday(elem) {
    let pathEdit = Routing.generate('print_info_holiday');
    let param = {
        id: $(elem).data("id"),
    }
    $.post(pathEdit, JSON.stringify(param), function (data) {
        let modalContent = $('#modalEditHoliday').find(".modalContent");
        modalContent.html(data);
        changeDate(modalContent, true);
        initiRadioButtonDayUpdate(false);
    }, 'json');
}

function editHoliday() {
    let pathEditHoliday = Routing.generate('edit_holiday');
    let $modalEdit = $('#modalEditHoliday');
    let valideBtn = $modalEdit.find('.btn_edit');
    let params = {
        id: $modalEdit.find('.id').val(),
    };

    addOrEditHoliday($modalEdit, pathEditHoliday, valideBtn, params);
}

function validateOrRefuseHoliday(validateOrRefuse) {
    let $modalEdit = $('#modalEditHoliday');
    let valideBtn = $modalEdit.find('.btn-check');
    if (validateOrRefuse == 'refuse' && $('#reason').val() == '') {
        valideBtn.attr('data-dismiss', '');
        $modalEdit.find('.input-reason').removeClass('hide');
        $modalEdit.find('.error-reason').removeClass('hide');
        $modalEdit.find('.error-reason').html('Si vous voulez refuser cette demande veuillez remplir une raison puis cliquez sur Refuser pour valider.');
        return false;
    }
    let param = {
        id: $modalEdit.find('.id').val(),
        reason: $('#reason').val(),
    }
    let path = Routing.generate(validateOrRefuse + '_holiday');

    $.post(path, JSON.stringify(param), function () {
        dataTableHoliday.ajax.reload();
    }, 'json');
    valideBtn.attr('data-dismiss', 'modal');
}
function initDateModalNew() {
    let $modalAdd = $('#modalAddHoliday');
    let currentDate = moment().format('YYYY-MM-DD');
    let $firstDate = $modalAdd.find('.firstDate');
    let startDate = $firstDate.val();
    let cp = $modalAdd.find('.cp');
    let ss = $modalAdd.find('.ss');
    let rtt = $modalAdd.find('.rtt');

    $firstDate[0].setAttribute('min', currentDate);
    $modalAdd.find('.secondDate')[0].setAttribute('min', currentDate);

    if (startDate == '') {
        cp.prop('disabled', true);
        ss.prop('disabled', true);
        rtt.prop('disabled', true);
        $modalAdd.find('.nbDaysLeft').parent().addClass('invisible');
    }
}

function countWeekDays(dateStart, dateEnd) {
    let nbrDays = 0;

    if (!isNaN(dateStart) && isNaN(dateEnd)) return 1;
    while (dateStart <= dateEnd) {
        if (dateStart.isoWeekday() !== 6 && dateStart.isoWeekday() !== 7 && !isPublicHoliday(dateStart)) {
            nbrDays += 1;
        }

        dateStart.add(1, 'days');
    }
    return nbrDays;
}

function disableInputs($modal) {
    let cp = $modal.find('.cp');
    let ss = $modal.find('.ss');
    let rtt = $modal.find('.rtt');
    ss.prop('disabled', true);
    cp.prop('disabled', true);
    rtt.prop('disabled', true);

    $modal.find('.nbDaysLeft').parent().addClass('invisible');
}

function calculateDays($modal, fromEdit) {
    let cp = $modal.find('.cp');
    let ss = $modal.find('.ss');
    let rtt = $modal.find('.rtt');
    ss.prop('disabled', false);
    cp.prop('disabled', false);
    rtt.prop('disabled', false);
    if (!fromEdit) {
        cp.val(0);
        ss.val(0);
        rtt.val(0);
    }
    let startDate = moment($modal.find('.firstDate').val());
    let endDate = moment($modal.find('.secondDate').val());
    let formatStart = startDate.format('YYYY-MM-DD');
    let formatEnd = endDate.format('YYYY-MM-DD');
    let nbrDays = countWeekDays(startDate, endDate);

    $modal.find('.secondDate')[0].setAttribute('min', formatStart);

    if (isNaN(nbrDays)) nbrDays = 1;

    if (nbrDays > 1 || (nbrDays === 1 && (formatStart !== formatEnd) && !isNaN(endDate))) {
        $modal.find('.radioDay').addClass('invisible');
        $modal.find('.radioAllDay').prop('checked', true);
        $modal.find('.number-decimal').attr('step', 1);
    } else {
        $modal.find('.radioDay').removeClass('invisible');
    }

    let $inputNbDays = $modal.find(".nbDaysLeft");
    $inputNbDays.text(nbrDays);
    $inputNbDays.parent().removeClass('invisible');

    if (formatStart > formatEnd) {
        $modal.find('.dateFin').val('');
    }
}

function changeDate($input, fromEdit = false) {
    let $modal = $input.closest('.modal');
    if ($modal.find('.firstDate').val() !== '') {
        calculateDays($modal, fromEdit);
    } else {
        disableInputs($modal);
    }
    $modal.find('.error-msg').html('');
}

function initiRadioButtonDayUpdate(forModalNew) {
    let $input = forModalNew ? $('input[type=radio][name=radioDayNew]') : $('input[type=radio][name=radioDayEdit]');

    $($input).change(function () {
        let nbDays = $(this).val() == 'day' ? 1 : 0.5;
        $modal = forModalNew ? $('#modalAddHoliday') : $('#modalEditHoliday');
        $modal.find(".nbDaysLeft").text(nbDays);

        if (nbDays == 0.5) {
            $input.closest('.modal').find('.number-decimal').attr('step', 0.5);
        } else {
            $input.closest('.modal').find('.number-decimal').attr('step', 1);
        }
    });
}

$('#submitFilterHolidays').on('click', function () {
    let statut = $('#statutFilter').val();
    let demandeur = $('#demandeurFilter').val();
    let validateur = $('#validateurFilter').val();

    dataTableHoliday
        .columns('Demandeur:name')
        .search(demandeur)
        .draw();

    dataTableHoliday
        .columns('Validateur:name')
        .search(validateur)
        .draw();

    dataTableHoliday
        .columns('Statut:name')
        .search(statut)
        .draw();

    $.fn.dataTable.ext.search.push(
        function (settings, data) {
            let dateMin = $('#dateMin').val();
            let dateMax = $('#dateMax').val();
            let indexDate = dataTableHoliday.column('Début:name').index();
            let dateInit = (data[indexDate]).split('/').reverse().join('-') || 0;
            if (
                (dateMin === "" && dateMax === "")
                ||
                (dateMin === "" && moment(dateInit).isSameOrBefore(dateMax))
                ||
                (moment(dateInit).isSameOrAfter(dateMin) && dateMax === "")
                ||
                (moment(dateInit).isSameOrAfter(dateMin) && moment(dateInit).isSameOrBefore(dateMax))

            ) {
                return true;
            }
            return false;
        }
    );
    dataTableHoliday
        .draw();
});

function isPublicHoliday(date) {
    let yiear = new Date(date);
    yiear = yiear.getFullYear();
    let publicHolidays = [
        moment('0101' + yiear, 'DD/MM/YYYY'),
        moment('0105' + yiear, 'DD/MM/YYYY'),
        moment('0805' + yiear, 'DD/MM/YYYY'),
        moment('1407' + yiear, 'DD/MM/YYYY'),
        moment('1508' + yiear, 'DD/MM/YYYY'),
        moment('0111' + yiear, 'DD/MM/YYYY'),
        moment('1111' + yiear, 'DD/MM/YYYY'),
        moment('2512' + yiear, 'DD/MM/YYYY'),
        moment.easter(yiear),
        moment.easter(yiear).add(49, 'days'),
        moment.easter(yiear).add(39, 'days'),
    ];

    return publicHolidays.filter(holiday => holiday.isSame(date)).length > 0;
}

function updateCurrentUserHolidays(data) {
    let $modalAdd = $('#modalAddHoliday');

    $modalAdd.find('.stock-cp').text(data.cp);
    $modalAdd.find('.stock-rtt').text(data.rtt);
}

function isHolidayOnStock($modal, params) {
    let holidayIsOnStock =
        Number(params.cp) <= $modal.find('.stock-cp').text()
        &&
        Number(params.rtt) <= $modal.find('.stock-rtt').text();

    return holidayIsOnStock;
}

function addOrEditHoliday($modal, path, valideBtn, params) {
    params.description = $modal.find('.description').val();
    params.firstDate = $modal.find('.firstDate').val();
    params.secondDate = $modal.find('.secondDate').val();
    params.cp = $modal.find('.cp').val();
    params.rtt = $modal.find('.rtt').val();
    params.ss = $modal.find('.ss').val();

    let required = $modal.find('.required');
    let requiredAreSet = true;

    $(required).each(function () {
        if ($(this).val() == '') {
            valideBtn.removeAttr('data-dismiss');
            $(this).addClass('emptyField');
            requiredAreSet = false;
        } else {
            $(this).removeClass('emptyField');
        }
    });

    let nbDaysLeft = Number($modal.find('.nbDaysLeft').text());

    if (requiredAreSet && nbDaysLeft === 0 && isHolidayOnStock($modal, params)) {
        valideBtn.attr('data-dismiss', 'modal');
        $.post(path, JSON.stringify(params), function (data) {
            updateCurrentUserHolidays(data);
            dataTableHoliday.ajax.reload();
        }, 'json');
    } else if (nbDaysLeft > 0) {
        valideBtn.attr('data-dismiss', '');
        $modal.find('.error-msg').html('Il vous reste des jours à poser !');
    } else if (nbDaysLeft < 0) {
        valideBtn.attr('data-dismiss', '');
        $modal.find('.error-msg').html('Vous avez posé plus de jours que nécessaire.');
    } else if (!isHolidayOnStock($modal, params)) {
        valideBtn.attr('data-dismiss', '');
        $modal.find('.error-msg').html('Vous avez posé plus de jours que vos jours disponibles.');
    }
}

function calculsMax($input)
{
    let $modal = $input.closest('.modal');

    let startDate = moment($modal.find('.firstDate').val());
    let endDate = moment($modal.find('.secondDate').val());

    let stockCp = $modal.find('.stock-cp').text();
    let stockRtt = $modal.find('.stock-rtt').text();

    let halfDay = $('.radioMorning').is(':checked') || $('.radioAfternoon').is(':checked');
    let totalDay = halfDay ? 0.5 : countWeekDays(startDate, endDate);

    let cp = $modal.find('.cp');
    let ss = $modal.find('.ss');
    let rtt = $modal.find('.rtt');
    let cpval = $modal.find('.cp').val();
    let ssval = $modal.find('.ss').val();
    let rttval = $modal.find('.rtt').val();

    let maxcp = totalDay - ssval - rttval;
    let maxrtt = totalDay - ssval - cpval;
    let maxss = totalDay - cpval - rttval;
    if (maxcp > stockCp) maxcp = stockCp;
    if (maxrtt > stockRtt) maxrtt = stockRtt;

    cp.attr('max', maxcp);
    ss.attr('max', maxss);
    rtt.attr('max', maxrtt);

    let sum = Number(cpval) + Number(ssval) + Number(rttval);
    let dayToTake = totalDay - sum;
    $modal.find(".nbDaysLeft").text(dayToTake);
}