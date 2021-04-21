function ajaxDO() {
    xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            $('#buttonModalDoSet').click();
        }
    }
    let data = $('#doForm').find('.data');
    let json = {};
    data.each(function () {
        let val = $(this).val();
        let name = $(this).attr("name");
        json[name] = val;
    });
    let Json = JSON.stringify(json);
    let path = Routing.generate('ajax_params', true);
    xhttp.open("POST", path, true);
    xhttp.send(Json);
}