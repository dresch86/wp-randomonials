function RandomonialAdminController() {
    jQuery(document.getElementById('randomonials_add_button')).click(this.showAddForm.bind(this));
    jQuery(document.getElementById('randomonials_select_all')).click(this.selectAllRandomonials.bind(this));

    this.elDataForm = document.getElementById('randomonials_data_form');
    this.elAuthorInput = document.getElementById('randmonial_author_field');
    this.elCommentInput = document.getElementById('randmonial_comment_field');
    this.elControlsTable = document.getElementById('randomonials_control_grid');
    this.elCustomFields = document.getElementsByClassName('randomonial-tag-html');
    this.aRandomonialsChk = document.getElementsByName('randomonials_selected[]');
    this.elActionResultBox = document.getElementById('randomonials_action_result');
    this.elSubmitResultBox = document.getElementById('randomonials_submit_result');
    this.elParameterEditor = document.getElementById('randomonials_edit_tag_param');

    if (this.elControlsTable !== null) {
        this.elRandomonialControlRows = this.elControlsTable.querySelectorAll('tbody > tr[data-randomonial-id]');

        for (let i=0;i<this.elRandomonialControlRows.length;i++) {
            this.elRandomonialControlRows[i].addEventListener('click', this.dataGridControlRouter.bind(this));
        }

        // There are 2 bulk apply buttons to handle actions for
        this.elBulkApplyButtons = document.getElementsByClassName('randomonials_bulk_action_apply');
        this.elBulkApplyButtons[0].addEventListener('click', this.handleBulkApply.bind(this));
        this.elBulkApplyButtons[1].addEventListener('click', this.handleBulkApply.bind(this));
    }

    this.elCustomFieldset = document.getElementById('randomonials_custom_fields');

    if (this.elCustomFieldset !== null) {
        let elCustomControls = this.elCustomFieldset.querySelectorAll('.randomonials-custom-tag-container > .randomonial-tag-html > button');

        for (let i=0; i<elCustomControls.length; i++) {
            jQuery(elCustomControls[i]).click(this.showParameterEditor.bind(this));
        }
    }

    this.oFormData = null;
};

RandomonialAdminController.prototype.capitalize = function(str) {
    return str.replace(/\w\S*/g, function(txt){
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
};

RandomonialAdminController.prototype.nl2br = function (str, replaceMode = false, isXhtml = false) {   
    let breakTag = (isXhtml) ? '<br />' : '<br>';
    let replaceStr = (replaceMode) ? '$1'+ breakTag : '$1'+ breakTag +'$2';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, replaceStr);
}

RandomonialAdminController.prototype.br2nl = function (str, replaceMode = true) {   
    var replaceStr = (replaceMode) ? "\n" : '';
    return str.replace(/<\s*\/?br\s*[\/]?>/gi, replaceStr);
}

RandomonialAdminController.prototype.dataGridControlRouter = function(event) {
    event.stopPropagation();

    if (event.target.classList.contains('randomonial-admin-btn-edit')) {
        this.showEditForm(event);
    } else if (event.target.classList.contains('randomonial-admin-btn-up')) {
        console.log('Moving up functionality not yet implemented.');
    } else if (event.target.classList.contains('randomonial-admin-btn-down')) {
        console.log('Moving down functionality not yet implemented.');
    } else if (event.target.classList.contains('randomonial-admin-btn-del')) {
        this.handleDeleteButton(event);
    } else {
        return;
    }
};

RandomonialAdminController.prototype.initializeDataObject = function(randomonial = null) {
    if (randomonial !== null) {
        this.oFormData = randomonial;
    } else {
        this.oFormData = 
        {
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
        
        for (let i=0;i<this.elCustomFields.length;i++) {
            this.oFormData[this.elCustomFields[i].dataset.fieldName] = {};
            this.oFormData[this.elCustomFields[i].dataset.fieldName]['value'] = "";
            this.oFormData[this.elCustomFields[i].dataset.fieldName]['attributes'] = [];
        }
    }
};

RandomonialAdminController.prototype.resetForm = function(clear_result_field = true) {
    this.initializeDataObject();
    this.elDataForm.reset();

    for (let i=0;i<this.elCustomFields.length;i++) {
        let elButton = this.elCustomFields[i].getElementsByTagName('button');

        for (let j=0;j<elButton.length;j++) {
            elButton[j].classList.remove('randomonial-tag-html-set');
            elButton[j].innerHTML = 'Set';

            if (('attrPos' in elButton[j].dataset)) {
                delete elButton[j].dataset.attrPos;
            }
        }
    }

    if (clear_result_field) {
        this.elSubmitResultBox.innerHTML = '';
        this.elSubmitResultBox.classList.remove('randomonial-show-result');
    }
};

RandomonialAdminController.prototype.selectAllRandomonials = function(event) {
    for (let i=0;i<this.aRandomonialsChk.length;i++) {
        if (event.target.checked) {
            this.aRandomonialsChk[i].checked = true;
        }
        else {
            this.aRandomonialsChk[i].checked = false;
        }
    }
};

RandomonialAdminController.prototype.handleDeleteButton = function(event) {
    let aItemIds = [event.target.parentNode.parentNode.dataset.randomonialId];
    this.ajaxDeleteRandomonials(aItemIds);
};

RandomonialAdminController.prototype.handleBulkApply = function(event) {
    event.stopPropagation();
    let elBulkSelector = event.target.parentNode.querySelector('select');

    if (elBulkSelector.value == 'delete') {
        let aDeleteIds = [];

        for (let i=0;i<this.aRandomonialsChk.length;i++) {
            if (this.aRandomonialsChk[i].checked) {
                aDeleteIds.push(this.aRandomonialsChk[i].value);
            }
        }

        this.ajaxDeleteRandomonials(aDeleteIds);
    }
};

RandomonialAdminController.prototype.ajaxNewRandomonial = function() {
    if (this.elAuthorInput.value.length > 1) {
        this.oFormData.author.value = this.elAuthorInput.value;
    }
    else {
        // TODO Error checking
    }

    if (this.elCommentInput.value.length > 1) {
        this.oFormData.comment.value = this.nl2br(this.elCommentInput.value);
    }
    else {
        // TODO Error checking
    }

    jQuery.post(
        randomonial_admin_client.ajax_url, 
        {
            "action"    : "randomonials",
            "operation" : "add-item",
            "wp_nonce"  : randomonial_admin_client.nonce_add_item,
            "fields"    : JSON.stringify(this.oFormData)
        }).done(response => {
            let aResult = JSON.parse(response);

            if (aResult[0] == 200) {
                this.elSubmitResultBox.innerHTML = 'New Randomonial Added!';
                this.elSubmitResultBox.classList.add('randomonial-show-result');
                this.resetForm(false);
            }
            else if (aResult[0] == 400) {
                this.elSubmitResultBox.innerHTML = 'Error - Field(s) missing / invalid!';
                this.elSubmitResultBox.classList.add('randomonial-show-result');
                this.elSubmitResultBox.classList.add('randomonial-show-result-error');
            }
            else {
                this.elSubmitResultBox.innerHTML = 'Error - ' + aResult[1];
                this.elSubmitResultBox.classList.add('randomonial-show-result');
                this.elSubmitResultBox.classList.add('randomonial-show-result-error');
            }
        });
};

RandomonialAdminController.prototype.ajaxEditRandomonial = function(id) {
    if (this.elAuthorInput.value.length > 1) {
        this.oFormData.author.value = this.elAuthorInput.value;
    }
    else {
        // TODO Error checking
    }

    if (this.elCommentInput.value.length > 1) {
        this.oFormData.comment.value = this.nl2br(this.elCommentInput.value);
    }
    else {
        // TODO Error checking
    }

    jQuery.post(
        randomonial_admin_client.ajax_url, 
        {
            "action"    : "randomonials",
            "operation" : "edit-item",
            "wp_nonce"  : randomonial_admin_client.nonce_edit_item,
            "itemId"    : id,
            "fields"    : JSON.stringify(this.oFormData)
        }).done(response => {
            let aResult = JSON.parse(response);

            if (aResult[0] == 200) {
                this.elSubmitResultBox.innerHTML = 'Successfully Updated!';
                this.elSubmitResultBox.classList.add('randomonial-show-result');
            }
            else if (aResult[0] == 400) {
                this.elSubmitResultBox.innerHTML = 'Error - Field(s) missing / invalid!';
                this.elSubmitResultBox.classList.add('randomonial-show-result');
                this.elSubmitResultBox.classList.add('randomonial-show-result-error');
            }
            else {
                this.elSubmitResultBox.innerHTML = 'Error - ' + aResult[1];
                this.elSubmitResultBox.classList.add('randomonial-show-result');
                this.elSubmitResultBox.classList.add('randomonial-show-result-error');
            }
        });
}

RandomonialAdminController.prototype.ajaxDeleteRandomonials = function(aItemIds) {
    jQuery.post(
        randomonial_admin_client.ajax_url, 
        {
            "action"    : "randomonials",
            "operation" : "delete-items",
            "items"     : JSON.stringify(aItemIds),
            "wp_nonce"  : randomonial_admin_client.nonce_delete_items
        }).done(response => 
        {
            let aResponse = JSON.parse(response);
            if (aResponse[0] == 200) {
                if (aResponse[1] == aItemIds.length) {
                    this.elActionResultBox.innerHTML = 'Successfully deleted ' + aItemIds.length + ' randomonial(s)!';
                }
                else {
                    this.elActionResultBox.innerHTML = 'Deleted ' + aResponse[1] + ' randomonials! Some of your selection could not be delete as they were most likely already removed by another administrator.'
                }
            } else {
                this.elActionResultBox.innerHTML = 'Request Error - ' + aResponse[1];
            }

            jQuery(this.elActionResultBox).dialog({
                title: 'Deletion Result',
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
                    "Close": () => {
                        jQuery(this.elActionResultBox).dialog('close');
                        window.location.reload(true);
                    }
                },
                open: () => {
                    // close dialog by clicking the overlay behind it
                    jQuery('.ui-widget-overlay').bind('click', () => {
                        jQuery(this.elActionResultBox).dialog('close');
                    });
                },
                create: () => {
                    // style fix for WordPress admin
                    jQuery('.ui-dialog-titlebar-close').addClass('ui-button');
                }
            });

            jQuery(this.elActionResultBox).dialog('open');
        });
};

RandomonialAdminController.prototype.showParameterEditor = function(event) {
    let sFieldTitle = event.target.parentNode.dataset.fieldName;
    let sFieldParam = event.target.dataset.fieldParam;
    let sDialogTitle = sFieldTitle.toUpperCase() + ' [' + sFieldParam + ']';
    let elPopupParamInput = this.elParameterEditor.querySelector('#randomonial_param_input');

    jQuery(this.elParameterEditor).dialog({
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
                    this.oFormData[sFieldTitle].value = elPopupParamInput.value;

                    if (elPopupParamInput.value < 1) {
                        event.target.classList.remove('randomonial-tag-html-set');
                        event.target.innerHTML = 'Set';
                    }
                    else {
                        event.target.classList.add('randomonial-tag-html-set');
                        event.target.innerHTML = 'Modify';
                    }
                }
                else {
                    if (!('attrPos' in event.target.dataset)) {
                        this.oFormData[sFieldTitle].attributes.push(sFieldParam + '=' + elPopupParamInput.value);
                        event.target.dataset.attrPos = this.oFormData[sFieldTitle].attributes.length - 1;
                    }
                    else {
                        this.oFormData[sFieldTitle].attributes[event.target.dataset.attrPos] = sFieldParam + '=' + elPopupParamInput.value;
                    }

                    if (elPopupParamInput.value < 1) {
                        event.target.classList.remove('randomonial-tag-html-set');
                        event.target.innerHTML = 'Set';

                        delete oFormData[sFieldTitle].attributes[event.target.dataset.attrPos];
                        delete event.target.dataset.attrPos;
                    }
                    else {
                        event.target.classList.add('randomonial-tag-html-set');
                        event.target.innerHTML = 'Modify';
                    }
                }

                jQuery(this.elParameterEditor).dialog('close');
            },
            "Cancel": () => {
                jQuery(this.elParameterEditor).dialog('close');
            }
        },
        open: () => {
            // close dialog by clicking the overlay behind it
            jQuery('.ui-widget-overlay').bind('click', () => {
                jQuery(this.elParameterEditor).dialog('close');
            });

            if (sFieldParam == 'value') {
                if (this.oFormData[sFieldTitle].value.length > 0) {
                    elPopupParamInput.value = this.oFormData[sFieldTitle].value;
                }
            }
            else {
                if (('attrPos' in event.target.dataset)) {
                    elPopupParamInput.value = (this.oFormData[sFieldTitle].attributes[event.target.dataset.attrPos].split('='))[1];
                }
            }
        },
        create: () => {
            // style fix for WordPress admin
            jQuery('.ui-dialog-titlebar-close').addClass('ui-button');
        },
        close: () => {
            elPopupParamInput.value = '';
        }
    });

    jQuery(this.elParameterEditor).dialog('open');
};

RandomonialAdminController.prototype.showAddForm = function() {
    jQuery(this.elDataForm).dialog({
        title: 'Add Randomonial',
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
            "Create": () => {
                this.ajaxNewRandomonial();
            },
            "Reset": () => {
                this.resetForm();
            },
            "Cancel": () => {
                jQuery('#randomonials_data_form').dialog('close');
            }
        },
        open: () => {
            this.resetForm();

            // close dialog by clicking the overlay behind it
            jQuery('.ui-widget-overlay').bind('click', () => {
                jQuery('#randomonials_data_form').dialog('close');
            });
        },
        create: () => {
            // style fix for WordPress admin
            jQuery('.ui-dialog-titlebar-close').addClass('ui-button');
        },
        close: () => {
            // Refresh the window for updates
            window.location.reload(true);

            // Cleanup after myself...
            this.resetForm();
        }
    });

    jQuery('#randomonials_data_form').dialog('open');
};

RandomonialAdminController.prototype.showEditForm = function(event) {
    let iItemId = event.currentTarget.dataset.randomonialId;

    jQuery.post(
        randomonial_admin_client.ajax_url, 
        {
            "action"    : "randomonials",
            "operation" : "get-item",
            "wp_nonce"  : randomonial_admin_client.nonce_get_item,
            "itemId"    : iItemId
        }).done(response => 
            {
                let aResult = JSON.parse(response);

                if (aResult[0] == 200) {
                    this.initializeDataObject(aResult[1]);
                    this.elAuthorInput.value = aResult[1]['author']['value'];
                    this.elCommentInput.value = this.br2nl(aResult[1]['comment']['value']);

                    // All attributes for custom fields should be set so update the control style...
                    for (let i=0;i<this.elCustomFields.length;i++) {
                        let elButtonControl = this.elCustomFields[i].querySelector('button[data-field-param="value"]');

                        if (elButtonControl !== null) {
                            elButtonControl.classList.add('randomonial-tag-html-set');
                            elButtonControl.innerHTML = 'Modify';
                        }

                        let aFieldAttributes = aResult[1][this.elCustomFields[i].dataset.fieldName]['attributes'];

                        for (let j=0;j<aFieldAttributes.length;j++) {
                            elButtonControl = this.elCustomFields[i].querySelector('button[data-field-param="' + aFieldAttributes[j].substring(0, aFieldAttributes[j].indexOf('=')) + '"]');
                            
                            if (elButtonControl !== null) {
                                elButtonControl.classList.add('randomonial-tag-html-set');
                                elButtonControl.dataset.attrPos = j;
                                elButtonControl.innerHTML = 'Modify';
                            }
                        };
                    }

                    jQuery(this.elDataForm).dialog({
                        title: 'Edit Randomonial',
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
                            "Apply": () => {
                                this.ajaxEditRandomonial(iItemId);
                            },
                            "Close": () => {
                                jQuery(this.elDataForm).dialog('close');
                            }
                        },
                        open: () => {
                            // close dialog by clicking the overlay behind it
                            jQuery('.ui-widget-overlay').bind('click', () => {
                                jQuery(this.elDataForm).dialog('close');
                            });
                        },
                        create: () => {
                            // style fix for WordPress admin
                            jQuery('.ui-dialog-titlebar-close').addClass('ui-button');
                        },
                        close: () => {
                            if (this.elSubmitResultBox.innerHTML.trim().length > 0) {
                                // Refresh the window since a change was made
                                window.location.reload(true);
                            }
                            
                            this.resetForm();
                        }
                    });

                    jQuery(this.elDataForm).dialog('open');
                }
                else {
                    this.elActionResultBox.innerHTML = 'Unable to locate selected randomonial. It may have been removed by another administrator.';

                    jQuery(this.elActionResultBox).dialog({
                        title: 'Retrieval Error',
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
                            "Close": () => {
                                jQuery(this.elActionResultBox).dialog('close');
                                window.location.reload(true);
                            }
                        },
                        open: () => {
                            // close dialog by clicking the overlay behind it
                            jQuery('.ui-widget-overlay').bind('click', () => {
                                jQuery(this.elActionResultBox).dialog('close');
                            });
                        },
                        create: () => {
                            // style fix for WordPress admin
                            jQuery('.ui-dialog-titlebar-close').addClass('ui-button');
                        }
                    });
        
                    jQuery(this.elActionResultBox).dialog('open');
                }
            });
};

jQuery(document).ready(() => {
    var rfcAdminHandler = new RandomonialAdminController();
});