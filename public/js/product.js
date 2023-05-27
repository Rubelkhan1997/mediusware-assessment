var currentIndex = 0;

var indexs = [];

$(document).ready(function () {
    addVariantTemplate();

    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var files = [];
    // File upload
    $("#file-upload").dropzone({
        url: "/dropzone",
        method: "post",
        headers: {'X-CSRF-TOKEN': csrfToken},
        maxFilesize: 2, // MB
        acceptedFiles: ".jpg,.png,.gif",
        addRemoveLinks: true,
        dictRemoveFile: "Remove",
        success: function (file, response) { 
            files.push(response.path);
            $('input[name=product_photo').val(files);
        },
        error: function (file, response) {
            console.log(response);
        }
    });
    // // File remove
    // $('.dropzone').on('click', '.dz-remove', function() {
    //     var filename = $(this).data('filename');

    //     // Send a DELETE request to the remove route
    //     $.ajax({
    //         url: '/upload/' + filename,
    //         type: 'DELETE',
    //         headers: {
    //             'X-CSRF-TOKEN': csrfToken
    //         },
    //         success: function(response) {
    //             console.log(response.message);
    //         }
    //     });
    // });
});

function addVariant(event) {
    event.preventDefault();
    addVariantTemplate();
}

function getCombination(arr, pre) {

    pre = pre || '';

    if (!arr.length) {
        return pre;
    }

    return arr[0].reduce(function (ans, value) {
        return ans.concat(getCombination(arr.slice(1), pre + value + '/'));
    }, []);
}

function updateVariantPreview(edit = 0) {

    var valueArray = [];

    $(".select2-value").each(function () {
        valueArray.push($(this).val());
    });

    var variantPreviewArray = getCombination(valueArray);
    var tableBody = '';

    if(edit == 1){return false;}
    $(variantPreviewArray).each(function (index, element) {
        tableBody += `<tr>
                        <th>
                            <input type="hidden" name="product_preview[${index}][variant]" value="${element}">
                            <span class="font-weight-bold">${element}</span>
                        </th>
                        <td>
                            <input type="number" name="product_preview[${index}][price]" value="0" class="form-control" required>
                        </td>
                        <td>
                            <input type="number" name="product_preview[${index}][stock]" value="0" class="form-control" required>
                        </td>
                      </tr>`;
    });

    $("#variant-previews").empty().append(tableBody);
}

function addVariantTemplate() {

    $("#variant-sections").append(`<div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Option</label>
                                        <select id="select2-option-${currentIndex}" data-index="${currentIndex}" name="product_variant[${currentIndex}][option]" class="form-control custom-select select2 select2-option" required>
                                            <option value="1">
                                                Color
                                            </option>
                                            <option value="2">
                                                Size
                                            </option>
                                            <option value="6">
                                                Style
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="d-flex justify-content-between">
                                            <span>Value</span>
                                            <a href="#" class="remove-btn" data-index="${currentIndex}" onclick="removeVariant(event, this);">Remove</a>
                                        </label>
                                        <select id="select2-value-${currentIndex}" data-index="${currentIndex}" name="product_variant[${currentIndex}][value][]" class="select2 select2-value form-control custom-select" multiple="multiple" required>
                                        </select>
                                    </div>
                                </div>
                            </div>`);

    $(`#select2-option-${currentIndex}`).select2({placeholder: "Select Option", theme: "bootstrap4"});

    $(`#select2-value-${currentIndex}`).select2({
        tags: true,
        multiple: true,
        placeholder: "Type tag name",
        allowClear: true,
        theme: "bootstrap4"

    }).on('change', function () {
        updateVariantPreview();
    });

    indexs.push(currentIndex);

    currentIndex = (currentIndex + 1);

    if (indexs.length >= 3) {
        $("#add-btn").hide();
    } else {
        $("#add-btn").show();
    }
}

function removeVariant(event, element) {

    event.preventDefault();

    var jqElement = $(element);

    var position = indexs.indexOf(jqElement.data('index'))

    indexs.splice(position, 1)

    jqElement.parent().parent().parent().parent().remove();

    if (indexs.length >= 3) {
        $("#add-btn").hide();
    } else {
        $("#add-btn").show();
    }

    updateVariantPreview();
}

