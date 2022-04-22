const Roles =
    {
        "NotAuthorized": 0,
        "User": 1,
        "Moder": 2,
        "Admin": 3
    }

const BASE_URL = 'http://localhost:81';
let token;


function ChangeNavBar(username) {
    if (localStorage.getItem('token') !== '' && username !== ''){
        $('.ChangeablePartNavBar ').empty();
        $('.ChangeablePartNavBar ').append('<li class="nav-item text-light"><a class="nav-link  h5 p-0 m-0 ms-5" href="../html/Profile.html">Привет, ' + username + '</a></li>');
        $('.ChangeablePartNavBar ').append('<li class="nav-item"><a id="Logout" class="nav-link  h5 p-0 m-0 ms-5" onclick="Logout()">Выход</a></li>');
    }

    else{
        $('.ChangeablePartNavBar ').empty();
        $('.ChangeablePartNavBar ').append('<li class="nav-item"><a class="nav-link  h5 p-0 m-0 ms-5" id="Login">Вход</a></li>');
        $('.ChangeablePartNavBar ').append('<li class="nav-item"><a class="nav-link  h5 p-0 m-0 ms-5" id="Register">Регистрация</a></li>');
    }
}




