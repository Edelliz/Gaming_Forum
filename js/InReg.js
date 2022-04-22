function CreateLoginModal() {
    $('#Login').attr('data-bs-toggle', "modal");
    $('#Login').attr('data-bs-target', "#modalLogin");

    $('#Login').before(
        '<div class="modal fade" id="modalLogin" tabindex="-1" aria-labelledby="modalLoginLabel" aria-hidden="true">' +
        '<div class="modal-dialog modal-dialog-centered">' +
        '<h6 class="modal-content border border-dark">' +
        '<div class="modal-header bg-dark d-flex justify-content-center">' +
        '<h3 class="modal-title text-light" id="modalLoginLabel">Вход</h3>' +
        '</div>' +
        '<div class="modal-body bg-secondary d-flex flex-column justify-content-center">' +
        '<div class="align-self-center text-center">' +
        '<h6>Логин</h6>' +
        '<input id="usernameLogin" class="form-control" type="text"  required>' +
        '</div>' +
        '<div class="align-self-center text-center mt-3">' +
        '<h6>Пароль</h6>' +
        '<input id="passwordLogin" class="form-control" type="password"  required>' +
        '</div>' +
        '</div>' +
        '<div class="modal-footer bg-dark  d-flex justify-content-center">' +
        '<button id="btnLogin" type="submit" class="btnProject btn btn-dark">Войти</button>' +
        '<button type="button" class="btnProject btn btn-dark" data-bs-dismiss="modal">Выйти</button>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>'
    )
}

CreateLoginModal()

function CreateRegisterModal() {
    $('#Register').attr('data-bs-toggle', "modal");
    $('#Register').attr('data-bs-target', "#modalRegister");

    $('#Register').before(
        '<div class="modal fade" id="modalRegister" tabindex="-1" aria-labelledby="modalRegisterLabel" aria-hidden="true">' +
            '<div class="modal-dialog modal-dialog-centered">' +
                '<div class="modal-content border border-dark">' +
                    '<div class="modal-header bg-dark d-flex justify-content-center">' +
                        '<h3 class="modal-title text-light" id="modalRegisterLabel">Регистрация</h3>' +
                    '</div>' +
                    '<div class="modal-body bg-secondary d-flex flex-column justify-content-center">' +
        '<div class="align-self-center text-center mt-3">' +
                        '<h6>Имя</h6>' +
                        '<input id="nameRegister" class="form-control" type="text"  required>' +
                    '</div>' +
        '<div class="align-self-center text-center mt-3">' +
                        '<h6>Фамилия</h6>' +
                        '<input id="surnameRegister" class="form-control" type="text"  required>' +
                    '</div>' +
                    '<div class="align-self-center text-center mt-3">' +
                        '<h6>Логин</h6>' +
                        '<input id="usernameRegister" class="form-control" type="text"  required>' +
                    '</div>' +
                    '<div class="align-self-center text-center mt-3">' +
                        '<h6>Пароль</h6>' +
                        '<input id="passwordRegister" class="form-control" type="password"  required>' +
                    '</div>' +
                '</div>' +
            '<div class="modal-footer bg-dark  d-flex justify-content-center">' +
                '<button id="btnRegister" type="submit" class="btnProject btn btn-dark">Зарегистрироваться</button>' +
                '<button type="button" class="btnProject btn btn-dark" data-bs-dismiss="modal">Выйти</button>' +
            '</div>' +
        '</div>' +
        '</div>' +
        '</div>'
    )
}

CreateRegisterModal()

let dataUser = {
    "Username": $('#usernameLogin').val(),
    "Password": $('#passwordLogin').val()
}

if(localStorage.getItem('token') !== null){
    Login();
}

$('#btnLogin').on('click', function () {
    Login();
})

function Login() {
    if(localStorage.getItem('token') === null){
        dataUser = {
            "Username": $('#usernameLogin').val(),
            "Password": $('#passwordLogin').val()
        }

        localStorage.setItem('username', $('#usernameLogin').val());
        localStorage.setItem('password', $('#passwordLogin').val());
    }
    else {
        dataUser = {
            "Username":  localStorage.getItem('username'),
            "Password": localStorage.getItem('password')
        }
    }

    $.ajax({
        url: BASE_URL + '/login',
        method: 'POST',
        contentType: "application/json",
        data: JSON.stringify(dataUser),
        success: function (data) {
            $("#modalLogin" ).modal('hide');
            localStorage.setItem('token', data.Token);
            localStorage.setItem('role', data.Role);
            localStorage.setItem('userID', data.UserId);
            ChangeNavBar(dataUser.Username);

        }
    })
}

$('#btnRegister').on('click', function () {
    Register();
})

function Register() {
    let dataUser = {
        "Name": $('#nameRegister').val(),
        "Surname": $('#surnameRegister').val(),
        "Username": $('#usernameRegister').val(),
        "Password": $('#passwordRegister').val()
    }

    $.ajax({
        url: BASE_URL + '/users',
        method: 'POST',
        contentType: "application/json",
        data: JSON.stringify(dataUser),
        success: function () {
            alert("Регистрация прошла успешно! Теперь вы можете войти в свой аккаунт");
            $("#modalRegister" ).modal('hide');
        }
    })
}

$('#Logout').on('click', function () {
    Logout();
})

function Logout(){
    $.ajax({
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        url: BASE_URL + '/logout',
        method: 'POST',
        contentType: "application/json",
        success: function () {
            localStorage.removeItem('username');
            localStorage.removeItem('password');
            localStorage.removeItem('token');
            localStorage.setItem('role', 0);
            ChangeNavBar('');
            location.reload()
        },
        error: function () {
            localStorage.removeItem('username');
            localStorage.removeItem('password');
            localStorage.removeItem('token');
            localStorage.setItem('role', 0);
            ChangeNavBar('');
            location.reload()

        }
    })
}

