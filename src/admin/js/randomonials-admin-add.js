var oFormData;
var elMainAddForm;
var elAuthorInput;
var elCommentInput;
var nlCustomFields;
var elPopupParamInput;
var elPopupParamEditor;

jQuery(document).ready(function() {
    elAuthorInput = document.getElementById('randmonial_author_ipt');
    elMainAddForm = document.getElementById('randomonials_add_form');
    elCommentInput = document.getElementById('randmonial_comment_ipt');
    elPopupParamInput = document.getElementById('randomonial_param_input');
    nlCustomFields = document.getElementsByClassName('randomonial-tag-html');
    elPopupParamEditor = document.getElementById('randomonials_edit_tag_param');

    jQuery(elMainAddForm).submit(event => 
    {
        event.preventDefault();

        if (elAuthorInput.value.length > 1) {
            oFormData.author.value = elAuthorInput.value;
        }
        else {

        }

        if (elCommentInput.value.length > 1) {
            oFormData.comment.value = elCommentInput.value;
        }
        else {

        }

        jQuery.post(
            randomonial_admin_client.ajax_url, 
            {
                "action"    : "randomonials",
                "operation" : "add-item",
                "wp_nonce"  : event.target.dataset.nonce,
                "fields"    : JSON.stringify(oFormData)
            }).done(response => {
                let aResult = JSON.parse(response);
                let elResultDisplay = document.getElementById('randomonials_submit_result');

                if (aResult[0] == 200) {
                    elResultDisplay.innerHTML = 'New Randomonial Added!';
                    elResultDisplay.classList.add('randomonial-show-result');

                    elMainAddForm.reset();
                    reset_form();
                }
                else if (aResult[0] == 400) {
                    elResultDisplay.innerHTML = 'Error - Field(s) missing / invalid!';
                    elResultDisplay.classList.add('randomonial-show-result');
                    elResultDisplay.classList.add('randomonial-show-result-error');
                }
                else {
                    elResultDisplay.innerHTML = 'Error - ' + aResult[1];
                    elResultDisplay.classList.add('randomonial-show-result');
                    elResultDisplay.classList.add('randomonial-show-result-error');
                }
            });
    });

    initialize_data_object();
});

function initialize_data_object() {
    oFormData = {
        author:
        {
            value:"",
            attributes:[]
        },
        comment:
        {
            value:"",
            attributes:[]
        }
    };

    for (let i=0;i<nlCustomFields.length;i++) {
        let sFieldTitle = nlCustomFields[i].dataset.fieldName;

        oFormData[sFieldTitle] = 
        {
            value:"",
            attributes:[]
        };
    }
}

function set_tag_param(_target) {
    let sFieldTitle = _target.parentNode.dataset.fieldName;
    let sFieldParam = _target.dataset.fieldParam;
    let sDialogTitle = sFieldTitle.toUpperCase() + ' [' + sFieldParam + ']';

    jQuery(elPopupParamEditor).dialog({
        title: sDialogTitle,
        dialogClass: 'wp-dialog',
        autoOpen: false,
        draggable: false,
        width: 'auto',
        modal: true,
        resizable: false,
        closeOnEscape: true,
        position: {
            my: "center",
            at: "center",
            of: window
        },
        buttons: {
            "Save": () => {
                if (sFieldParam == 'value') {
                    oFormData[sFieldTitle].value = elPopupParamInput.value;

                    if (elPopupParamInput.value < 1) {
                        _target.classList.remove('randomonial-tag-html-set');
                        _target.innerHTML = 'Set';
                    }
                    else {
                        _target.classList.add('randomonial-tag-html-set');
                        _target.innerHTML = 'Modify';
                    }
                }
                else {
                    if (!('attrPos' in _target.dataset)) {
                        oFormData[sFieldTitle].attributes.push(sFieldParam + '=' + elPopupParamInput.value);
                        _target.dataset.attrPos = oFormData[sFieldTitle].attributes.length - 1;
                    }
                    else {
                        oFormData[sFieldTitle].attributes[_target.dataset.attrPos] = sFieldParam + '=' + elPopupParamInput.value;
                    }

                    if (elPopupParamInput.value < 1) {
                        _target.classList.remove('randomonial-tag-html-set');
                        _target.innerHTML = 'Set';

                        delete oFormData[sFieldTitle].attributes[_target.dataset.attrPos];
                        delete _target.dataset.attrPos;
                    }
                    else {
                        _target.classList.add('randomonial-tag-html-set');
                        _target.innerHTML = 'Modify';
                    }
                }

                elPopupParamInput.value = '';
                jQuery(elPopupParamEditor).dialog('close');
            },
            "Cancel": () => {jQuery(elPopupParamEditor).dialog('close');}
        },
        open: function () {
            // close dialog by clicking the overlay behind it
            jQuery('.ui-widget-overlay').bind('click', function(){
                jQuery(elPopupParamEditor).dialog('close');
            })

            if (sFieldParam == 'value') {
                if (oFormData[sFieldTitle].value.length > 0) {
                    elPopupParamInput.value = oFormData[sFieldTitle].value;
                }
            }
            else {
                if (('attrPos' in _target.dataset)) {
                    elPopupParamInput.value = (oFormData[sFieldTitle].attributes[_target.dataset.attrPos].split('='))[1];
                }
            }
        },
        create: function () {
            // style fix for WordPress admin
            jQuery('.ui-dialog-titlebar-close').addClass('ui-button');
        }
    });

    jQuery(elPopupParamEditor).dialog('open');
}

function reset_form() {
    initialize_data_object();

    for (let i=0;i<nlCustomFields.length;i++) {
        let elButton = nlCustomFields[i].getElementsByTagName('button');

        for (let j=0;j<elButton.length;j++) {
            elButton[j].classList.remove('randomonial-tag-html-set');
            elButton[j].innerHTML = 'Set';

            if (('attrPos' in elButton[j].dataset)) {
                delete elButton[j].dataset.attrPos;
            }
        }
    }
}