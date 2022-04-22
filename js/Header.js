function CreateNavBar(){
    $('.container').before(
        '<header>'+
            '<nav className="navbar navbar-dark bg-dark navbar-expand navbar-toggleable mb-3 w-100">'+
               '<div className="container-fluid w-100">'+
                    '<img src="../img/eye.png" className="logo position-absolute pb-2"/>'+
                    '<a className="siteName navbar-brand ms-5 ps-4" href="../html/PopularSections.html">'+
                        '<h2>Gaming Forum</h2>'+
                    '</a>'+
                    '<button className="navbar-toggler" type="button" data-bs-toggle="collapse"'+
                            'data-bs-target=".navbar-collapse"'+
                            'aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">'+
                       ' <span className="navbar-toggler-icon"></span>'+
                   '</button>'+

                    '<div className="navbar-collapse collapse d-inline-flex justify-content-between w-100">'+
                        '<ul className="navbar-nav flex-grow-1">'+
                            '<li className="nav-item">'+
                                '<a className="nav-link  h5 p-0 m-0 ms-5" href="../html/AllSections.html">Разделы</a>'+
                           ' </li>'+
                            '<li className="nav-item">'+
                                '<a className="nav-link  h5 p-0 m-0 ms-5" href="../html/Announcement.html">Объявления</a>'+
                            '</li>'+
                        '</ul>'+

                        '<ul className="ChangeablePartNavBar navbar-nav flex-grow-1 d-flex justify-content-end">'+
                            '<li className="nav-item">'+
                                '<a className="nav-link  h5 p-0 m-0 ms-5" href="../html/Login.html">Вход</a>'+
                            '</li>'+
                            '<li className="nav-item">'+
                                '<a className="nav-link  h5 p-0 m-0 ms-5" href="../html/Register.html">Регистрация</a>'+
                            '</li>'+
                        '</ul>'+
                    '</div>'+
                '</div>'+
            '</nav>'+
       '</header>'
    )
}

CreateNavBar();