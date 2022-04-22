
function SetNameOfSection(){
    $('#nameOfSection').text(localStorage.getItem('TitleSection'));
}

function GetPosts(){
    $.ajax({
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        url: BASE_URL + '/categories/' + localStorage.getItem('SectionID'),
        contentType: "application/json",
        method: 'GET',
        cache: true,
        dataType: 'JSON',
        success: function (data){
            BuildBlockWithPosts(data.Posts);
        }
    })
}
function BuildBlockWithPosts(data){
    $('#tOrderPosts').empty();

    $template = $('#templatePostInOrder');

    for(let post of data)
    {
        $newPost = $template.clone();
        $newPost.removeClass('d-none');
        $newPost.attr('id', 'post-' + post.Id);
        $newPost.find('.TitlePost').text(post.Title);

        $newPost.find('#btnDeletePostInOrder').attr('id', 'btnDeletePostInOrder-' + post.Id);
        $newPost.find('#btnDeletePostInOrder-' + post.Id).attr('data-bs-target', '#modalDeletePostInOrder-' + post.Id);

        $newPost.find('#modalDeletePostInOrder').attr('id', 'modalDeletePostInOrder-' + post.Id);
        $newPost.find('#modalDeletePostInOrder-' + post.Id).attr('aria-labelledby', 'modalDeletePostInOrderLabel-' + post.Id);
        $newPost.find('#modalDeletePostInOrderLabel').attr('id', 'modalDeletePostInOrderLabel-' + post.Id);

        $newPost.find('#delete-post-modal-body-name').attr('id', 'delete-post-modal-body-name-' + post.Id);
        $newPost.find('#delete-post-modal-body-name-'+ post.Id).text('Название: ' + post.Title);
        

        $newPost.find('#bntDelPostInOrderSubmit').attr('id', 'bntDelPostInOrderSubmit-' + post.Id);
        $newPost.find('#bntDelPostInOrderSubmit-' + post.Id).on('click', function (){
            DeletePosts(post.Id);
        });
        
        $newPost.find('#titlePost').attr('id', 'titlePost-' + post.Id);
        $newPost.find('#titlePost-' + post.Id).attr('href', '../html/Post.html');
        $newPost.find('#titlePost-' + post.Id).on('click', function (){
            localStorage.setItem('TitlePost', post.Title);
            localStorage.setItem('PostID', post.Id);
        });

        $newPost.find('#textPost').attr('id', 'textPost-' + post.Id);
        $newPost.find( '#textPost-' + post.Id).text(post.Text);

        $newPost.find('#createdPost').attr('id', 'createdPost-' + post.Id);
        $newPost.find('#createdPost-' + post.Id).text(post.Created);

        $('#tOrderPosts').append($newPost);
    }
}
SetNameOfSection()
GetPosts()

function DeletePosts(id){
    $.ajax({
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        url: BASE_URL + '/Posts/'+id,
        method: 'DELETE',
        contentType: "application/json",
        success: function () {
            $("#modalDeletePostInOrder-" + id).modal('hide');
            GetPosts();
        },
        error: function () {
            $("#modalDeletePostInOrder-" + id).modal('hide');
            GetPosts();
        }
    })
}

$('#bntCreatePostSubmit').on('click', function (){
     CreatePosts();
});

function CreatePosts(){
    let newText = $('#create-post-modal-body-text').val();
    let newTitle= $('#create-post-modal-body-name').val();

    $.ajax({
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        url: BASE_URL + '/posts/',
        method: 'POST',
        contentType: "application/json",
        data:JSON.stringify({"Title": newTitle, "Text": newText,"CategoryId": localStorage.getItem("SectionID")}),
        success: function () {
            $("#modalCreatePost").modal('hide');
            GetPosts();
        },
        error: function (error) {
            console.log(error)
            $("#modalCreatePost").modal('hide');
            GetPosts();
        }
    })
}

function isCanEdit(){
    if(localStorage.getItem('role') == Roles.Admin){
        $('#aCreatePost').attr('class', 'p-0 m-0 text-light');
        $('#adminEditPlace').attr('class', '');
    }
}

isCanEdit();