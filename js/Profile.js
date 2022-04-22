$('#btnChangeAvatar').on('click', function () {
    $('#avatarInput').trigger('click');
});

$("#avatarInput").on('change', function () {
    UploadAvatar();
});

function UploadAvatar() {
    var fd = new FormData();
    var files = $('#avatarInput')[0].files;

    if (files.length > 0) {
        fd.append('File', files[0]);

        $.ajax({
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            },
            contentType: false,
            processData: false,
            data: fd,
            url: BASE_URL + '/users/' + localStorage.getItem('userID') + '/avatar',
            method: 'POST',
            success: function (response) {
                $("#profileImage").attr("src", ".." + response[0].Avatar)
                GetInfoUser()
            }
        })
    }
}

function GetInfoUser(){
    $.ajax({
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        url: BASE_URL + '/users/' + localStorage.getItem('userID'),
        contentType: "application/json",
        method: 'GET',
        cache: true,
        dataType: 'JSON',
        success: function (data) {
            console.log(data[0])
            BuildBlockProfile(data[0]);
        }
    })
}

function BuildBlockProfile(data){
    $('#username').text(data.Username);
    $('#name-surname').text(data.Surname + ' ' + data.Name);

    if(data.Avatar !== null){
        $("#profileImage").attr("src", ".." + data.Avatar)
    }
}

GetInfoUser();