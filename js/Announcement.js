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
        $newSection.find('.TextSection').text(section.Text);

        $newSection.find('#btnDeleteSection').attr('id', 'btnDeleteSection-' + section.Id);
        $newSection.find('#btnDeleteSection-' + section.Id).attr('data-bs-target', '#modalDeleteSection-' + section.Id);

        $newSection.find('#modalDeleteSection').attr('id', 'modalDeleteSection-' + section.Id);
        $newSection.find('#modalDeleteSection-' + section.Id).attr('aria-labelledby', 'modalDeleteSectionLabel-' + section.Id);
        $newSection.find('#modalDeleteSectionLabel').attr('id', 'modalDeleteSectionLabel-' + section.Id);

        $newSection.find('#delete-modal-body-name').attr('id', 'delete-modal-body-name-' + section.Id);
        $newSection.find('#delete-modal-body-name-'+ section.Id).text('Название: ' + section.Title);

        $newSection.find('#delete-modal-body-text').attr('id', 'delete-modal-body-text-' + section.Id);
        $newSection.find('#delete-modal-body-text-'+ section.Id).text('Описание: ' + section.Text);

        $newSection.find('#numberPost').attr('id', 'numberPost-' + section.Id);
        $newSection.find('#numberPost-' + section.Id).text(section.Created);

        $newSection.find('#btnEditSection').attr('id', 'btnEditSection-' + section.Id);
        $newSection.find('#btnEditSection-' + section.Id).attr('data-bs-target', '#modalEditSection-' + section.Id);

        $newSection.find('#modalEditSection').attr('id', 'modalEditSection-' + section.Id);
        $newSection.find('#modalEditSection-' + section.Id).attr('aria-labelledby', 'modalEditSectionLabel-' + section.Id);
        $newSection.find('#modalEditSectionLabel').attr('id', 'modalEditSectionLabel-' + section.Id);

        $newSection.find('#edit-modal-body-name').attr('id', 'edit-modal-body-name-' + section.Id);
        $newSection.find('#edit-modal-body-name-' + section.Id).attr("value", section.Title);

        $newSection.find('#edit-modal-body-text').attr('id', 'edit-modal-body-text-' + section.Id);
        $newSection.find('#edit-modal-body-text-' + section.Id).attr("value", section.Text);

        $newSection.find('#bntDelSubmit').attr('id', 'bntDelSubmit-' + section.Id);
        $newSection.find('#bntDelSubmit-' + section.Id).on('click', function (){
            DeleteSections(section.Id);
        });

        $newSection.find('#bntEdSubmit').attr('id', 'bntEdSubmit-' + section.Id);
        $newSection.find('#bntEdSubmit-' + section.Id).on('click', function (){
            EditSections(section.Id);
        });


        $('#tOrderSections').append($newSection);
    }
}

GetAnnouncement();

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
        url: BASE_URL + '/announcements/'+id,
        method: 'DELETE',
        contentType: "application/json",
        success: function () {
            $("#modalDeleteSection-" + id).modal('hide');
            GetAnnouncement();
        }
    })
}

function EditSections(id){
    let data = {
        "Title": $('#edit-modal-body-name-' + id).val(),
        "Text": $('#edit-modal-body-text-' + id).val()
    }

    $.ajax({
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        url: BASE_URL + '/announcements/'+id,
        method: 'PATCH',
        contentType: "application/json",
        data:JSON.stringify(data),
        success: function () {
            $("#modalEditSection-" + id).modal('hide');
            GetAnnouncement();
        },
        error: function () {
            $("#modalEditSection-" + id).modal('hide');
            GetAnnouncement();
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
        url: BASE_URL + '/announcements/',
        method: 'POST',
        contentType: "application/json",
        data:JSON.stringify({"Title": newText}),
        success: function () {
            $("#modalCreateSection").modal('hide');
            GetAnnouncement();
        },
        error: function () {
            $("#modalCreateSection").modal('hide');
            GetAnnouncement();
        }
    })
}