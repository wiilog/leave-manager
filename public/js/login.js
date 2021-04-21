function newUser(elem) {
        let $parent = elem.closest('.box-login');
        let name = $parent.find('#nom').val();
        let firstname = $parent.find('#prenom').val();
        let email = $parent.find('#email').val();
        let password = $parent.find('#password').val();
        let params = {name: name, firstname: firstname, email: email, password: password};
        let path = Routing.generate('register_user');

        if (name == "" || firstname == "" || email == "" || password == "") {
                $('#error_credentials').html('Tous les champs sont obligatoires.');
        } else {
                $.post(path, JSON.stringify(params), function(data) {
                        if (data == 'created') {
                                window.location.href = '/login';
                        }
                        else if (data == 'used') {
                                $('#error_credentials').html('Email déjà utilisé.');
                        }
                }, 'json');
        }
}