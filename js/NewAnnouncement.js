function GetAnnouncement(){
    $.ajax({
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        url:BASE_URL + '/announcements',
        contentType: "application/json",
        method: 'GET',
        cache: true,
        dataType: 'JSON',
        success: function (data){
            BuildBlockWithNewAnnouncements(data);
        }
    })
}

GetAnnouncement()

function BuildBlockWithNewAnnouncements(data){
    for(let i =0; i <= 4; i++){
        $('#newAnnouncements').append('<p>' + data[i].Title + ' - ' + data[i].Created +'</p>')
    }
}