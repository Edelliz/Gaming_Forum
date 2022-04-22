
function GetSections(){
    $.ajax({
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        url:BASE_URL + '/categories',
        contentType: "application/json",
        method: 'GET',
        cache: true,
        dataType: 'JSON',
        success: function (data){
            BuildBlockWithSection(data);
        }
    })
}

function BuildBlockWithSection(data){
    $('#tOrderSections').empty();

    $template = $('#templateSection');

    for(let section of data)
    {
        $newSection = $template.clone();
        $newSection.removeClass('d-none');
        $newSection.attr('id', 'section-' + section.Id);
        $newSection.find('.TitleSection').text(section.Title);

        $newSection.find('#btnDeleteSection').attr('id', 'btnDeleteSection-' + section.Id);
        $newSection.find('#btnDeleteSection-' + section.Id).attr('data-bs-target', '#modalDeleteSection-' + section.Id);

        $newSection.find('#modalDeleteSection').attr('id', 'modalDeleteSection-' + section.Id);
        $newSection.find('#modalDeleteSection-' + section.Id).attr('aria-labelledby', 'modalDeleteSectionLabel-' + section.Id);
        $newSection.find('#modalDeleteSectionLabel').attr('id', 'modalDeleteSectionLabel-' + section.Id);

        $newSection.find('#delete-modal-body-name').attr('id', 'delete-modal-body-name-' + section.Id);
        $newSection.find('#delete-modal-body-posts').attr('id', 'delete-modal-body-posts-' + section.Id);
        $newSection.find('#delete-modal-body-name-'+ section.Id).text('Название: ' + section.Title);
        $newSection.find('#delete-modal-body-posts-'+ section.Id).text('Количество постов: ' + section.Posts);

        $newSection.find('#numberPost').attr('id', 'numberPost-' + section.Id);
        $newSection.find('#numberPost-' + section.Id).text(section.Posts);

        $newSection.find('#btnEditSection').attr('id', 'btnEditSection-' + section.Id);
        $newSection.find('#btnEditSection-' + section.Id).attr('data-bs-target', '#modalEditSection-' + section.Id);

        $newSection.find('#modalEditSection').attr('id', 'modalEditSection-' + section.Id);
        $newSection.find('#modalEditSection-' + section.Id).attr('aria-labelledby', 'modalEditSectionLabel-' + section.Id);
        $newSection.find('#modalEditSectionLabel').attr('id', 'modalEditSectionLabel-' + section.Id);

        $newSection.find('#edit-modal-body-name').attr('id', 'edit-modal-body-name-' + section.Id);
        $newSection.find('#edit-modal-body-name-' + section.Id).attr("value", section.Title);

        $newSection.find('#bntDelSubmit').attr('id', 'bntDelSubmit-' + section.Id);
        $newSection.find('#bntDelSubmit-' + section.Id).on('click', function (){
            DeleteSections(section.Id);
        });

        $newSection.find('#bntEdSubmit').attr('id', 'bntEdSubmit-' + section.Id);
        $newSection.find('#bntEdSubmit-' + section.Id).on('click', function (){
            EditSections(section.Id);
        });

        $newSection.find('#titleSection').attr('id', 'titleSection-' + section.Id);
        $newSection.find('#titleSection-' + section.Id).attr('href', '../html/Section.html');
        $newSection.find('#titleSection-' + section.Id).on('click', function (){
            localStorage.setItem('TitleSection', section.Title);
            localStorage.setItem('SectionID', section.Id);
        });

        $('#tOrderSections').append($newSection);
    }
}

GetSections();

function isCanEdit(){
    if(localStorage.getItem('role') == Roles.Admin){
        $('#aCreateSection').attr('class', 'p-0 m-0 text-light');
        $('#adminEditPlace').attr('class', 'd-flex justify-content-center');
    }
}

isCanEdit();

function DeleteSections(id){
    $.ajax({
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        url: BASE_URL + '/categories/'+id,
        method: 'DELETE',
        contentType: "application/json",
        success: function () {
            $("#modalDeleteSection-" + id).modal('hide');
            GetSections();
        }
    })
}

function EditSections(id){
    let newText = $('#edit-modal-body-name-' + id).val();
    $.ajax({
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        url: BASE_URL + '/categories/'+id,
        method: 'PATCH',
        contentType: "application/json",
        data:JSON.stringify({"Title": newText}),
        success: function () {
            $("#modalEditSection-" + id).modal('hide');
            GetSections();
        },
        error: function () {
            $("#modalEditSection-" + id).modal('hide');
            GetSections();
        }
    })
}

$('#bntCreateSubmit').on('click', function (){
    CreateSections();
});

function CreateSections(){
    let newText = $('#create-modal-body-name').val();

    $.ajax({
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        url: BASE_URL + '/categories/',
        method: 'POST',
        contentType: "application/json",
        data:JSON.stringify({"Title": newText}),
        success: function () {
            $("#modalCreateSection").modal('hide');
            GetSections();
        },
        error: function () {
            $("#modalCreateSection").modal('hide');
            GetSections();
        }
    })
}