let canEdit = false;

function GetDetailsPost() {
    $.ajax({
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        url: BASE_URL + '/posts/' + localStorage.getItem('PostID'),
        contentType: "application/json",
        method: 'GET',
        cache: true,
        dataType: 'JSON',
        success: function (data) {
            canEdit = data.CanEdit || localStorage.getItem('role') == Roles.Admin;

            isCanComment();
            isCanEdit();

            BuildBlockPost(data);
        }
    })
}

GetDetailsPost();

function BuildBlockPost(data) {
    $('#namePost').text(data.Title)
    $('#usernamePost').text(data.OwnerUsername)
    $('#sectionNamePost').text(localStorage.getItem("TitleSection"))
    $('#createdPost').text(data.Created)
    $('.blogPostText').text(data.Text)

    $('.photoPlace').empty();
    for (let img of data.Photos){
        $('.photoPlace').append('<img style="height: 30%; width: 30%" src=..' + img.Link +' />')
    }

    $('.blog-posts-items').empty();
    $templateComment = $('#commentTemplate');
    let counter = 0;

    for (let comment of data.Comments){
        $newComment = $templateComment.clone();
        $newComment.attr('class', 'blog-post panel panel-default rounded bg-light border border-2 border-dark mt-3')
        $newComment.attr('id', 'comment-' + counter);
        $newComment.find("#InfoComment").attr("id", "InfoComment-" + counter);
        $newComment.find("#InfoComment-" + counter).text(comment.User);

        $newComment.find("#DateComment").attr("id", "DateComment-" + counter);
        $newComment.find("#DateComment-" + counter).text(comment.Created);

        $newComment.find("#textComment").attr("id", "textComment-" + counter);
        $newComment.find("#textComment-" + counter).text(comment.Text);

        $('.blog-posts-items').append($newComment);
    }

    $('#delete-modal-body-name').text('Название: ' + data.Title);
    $('#delete-modal-body-text').text('Описание: ' + data.Text);
    $('#delete-modal-body-created').text('Дата создания: ' + data.Created);
    $('#delete-modal-body-owner').text('Создатель: ' + data.OwnerUsername);

    $('#bntDelPostSubmit').on('click', function (){
        DeletePosts();
    });

    $('#edit-modal-body-name').val(data.Title);
    $('#edit-modal-body-text').val(data.Text);

    $('#bntEdSubmit').on('click', function (){
        EditSections();
    });
}

function EditSections(){
    let newName = $('#edit-modal-body-name').val();
    let newText = $('#edit-modal-body-text').val();

    $.ajax({
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        url: BASE_URL + '/Posts/'+ localStorage.getItem('PostID'),
        method: 'PATCH',
        contentType: "application/json",
        data:JSON.stringify({"Title": newName, "Text": newText}),
        success: function () {
            $("#modalEditSection").modal('hide');
            GetDetailsPost();
        },
        error: function () {
            $("#modalEditSection").modal('hide');
            GetDetailsPost();
        }
    })
}

function DeletePosts(){
    $.ajax({
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        url: BASE_URL + '/Posts/'+ localStorage.getItem('PostID'),
        method: 'DELETE',
        contentType: "application/json",
        success: function () {
            $("#modalDeleteSection").modal('hide');
            window.location.href = "../html/AllSections.html";
        },
        error: function () {
            $("#modalDeleteSection").modal('hide');
            window.location.href = "../html/AllSections.html";
        }
    })
}

$('#submitComment').on('click', function (){
    ToComment();
})

function ToComment() {
    let newText = $('#inputComment').val();
    $.ajax({
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        url: BASE_URL + '/posts/' + + localStorage.getItem('PostID') + '/comment',
        method: 'POST',
        contentType: "application/json",
        data:JSON.stringify({"Comment": newText}),
        success: function () {
            GetDetailsPost();
            $('#inputComment').val('');
        },
        error: function () {
            GetDetailsPost();
            $('#inputComment').val('');
        }
    })
}

function AddAnAttachment() {
    var fd = new FormData();
    var files = $('#file')[0].files;

    if (files.length > 0) {
        fd.append('File', files[0]);

        $.ajax({
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            },
            contentType: false,
            processData: false,
            data: fd,
            url: BASE_URL + '/posts/' + localStorage.getItem('PostID') + '/photo',
            method: 'POST',
            success: function (response) {
                $('#myform').attr('class', 'mt-4 text-end d-none');
                GetDetailsPost();
            }
        })
    }
}

function isCanEdit() {
    if (canEdit) {
        $('#adminEditPlace').attr('class', '');

        $("#but_load").attr('class', 'btnProject btn btn-dark align-self-end mt-3')


        $("#but_load").on('click', function () {
            $('#myform').attr('class', 'mt-4 text-end');
        });

        $("#but_upload").on('click', function () {
            AddAnAttachment();
        });

        $("#file").on('change', function () {
            AddAnAttachment();
        });
    }
}

function isCanComment() {
    if(localStorage.getItem('role') == Roles.NotAuthorized){
        $('#ToComment').attr('class', 'd-none')
        $('#abilityToComment').attr('class', '');
    }
    else {
        $('#ToComment').attr('class', '')
        $('#abilityToComment').attr('class', 'd-none');
    }
}




