var elDataTable;
var elEditButtons;

jQuery(document).ready(function() {
    elDataTable = document.getElementById('randomonials_datagrid');
    elEditButtons = document.getElementsByClassName('randomonial-admin-btn-edit');

    for (let i=0;i<elEditButtons.length;i++) {
        elEditButtons[i].addEventListener('click', show_edit_form);
    }
});

function selectAllRandomonials(_target) {
    let aRandomonialsChk = document.getElementsByName('randomonials_selected[]');

    for (let i=0;i<aRandomonialsChk.length;i++) {
        if (_target.checked) {
            aRandomonialsChk[i].checked = true;
        }
        else {
            aRandomonialsChk[i].checked = false;
        }
    }
}

function deleteMultiRandomonials() {

}

function deleteRandomonial(targetNode) {
    let iItemId = targetNode.parentNode.parentNode.dataset.randomonialId;
    let sItemNonce = targetNode.dataset.nonce;

    jQuery.post(
        randomonial_admin_client.ajax_url, 
        {
            "action"    : "randomonials",
            "operation" : "delete-item",
            "itemId"    : iItemId,
            "wp_nonce"  : sItemNonce
        }).done(response => 
        {
            let aResponse = JSON.parse(response);
            
            if ((aResponse[0] == 200) && (aResponse[1] == 'OK')) {
                let elDeleteRow = elDataTable.querySelectorAll('tbody > tr[data-randomonial-id=\'' + iItemId + '\']');

                if (elDeleteRow.length == 1) {
                    elDeleteRow[0].parentNode.removeChild(elDeleteRow[0]);
                    let elRandomonialRows = elDataTable.querySelectorAll('tbody > tr');

                    if (elRandomonialRows.length == 0) {
                        let elNoticeDiv = document.createElement('div');
                        elNoticeDiv.className = 'randomonials-notice';
                        elNoticeDiv.innerHTML = 'There are no randomonials to manage. Click the "Add New" button above to add one!';

                        let elTableParent = elDataTable.parentNode;
                        elTableParent.removeChild(elDataTable);
                        elTableParent.addChild(elNoticeDiv);
                    }
                    else { // Update the table...
                        for (let i=0;i<elRandomonialRows.length;i++) {
                            // Re-index rows after removing one instead of refreshing page
                            elRandomonialRows[i].dataset.randomonialId = i;
                        }
    
                        let elFirstMoveUpBtn = elRandomonialRows[0].querySelectorAll('td:nth-of-type(4) > button');
                        elFirstMoveUpBtn[0].disabled = true;
    
                        let elLastMoveDownBtn = elRandomonialRows[(elRandomonialRows.length-1)].querySelectorAll('td:nth-of-type(5) > button');
                        elLastMoveDownBtn[0].disabled = true;
                    }
                }
                else if (elDeleteRow.length < 1) {
                    console.error('The requested randomonial-id was deleted already?');
                }
                else {
                    console.error('Multiple randomonial-id values were found!');
                }
            }
        });
}

function show_edit_form(event) {
    event.stopPropagation();
    let iItemId = event.target.parentNode.parentNode.dataset.randomonialId;

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
                let efbEditFormHandler = new EditFormBuilder();

                if (aResult[0] == 200) {
                    let aFields = Object.keys(aResult[1]['RANDOMONIAL']);
                    aFields.forEach(field => {
                        if (field == 'author') {
                            efbEditFormHandler.setAuthor(aResult[1]['RANDOMONIAL']['author']['value']);
                        }
                        else if (field == 'comment') {
                            efbEditFormHandler.setComment(aResult[1]['RANDOMONIAL']['comment']['value']);
                        }
                        else {
                            efbEditFormHandler.addCustomField(field, aResult[1]['TEMPLATE'][field], aResult[1]['RANDOMONIAL'][field]);
                        }
                    });

                    let elEditForm = document.createElement('form');
                    elEditForm.classList.add('randomonials-vbox');
                    elEditForm.style.display = 'none';
                    elEditForm.appendChild(efbEditFormHandler.getCoreFieldset());

                    let elCustomFieldSet = efbEditFormHandler.getCustomFieldset();
                    if (elCustomFieldSet !== null) {
                        elEditForm.appendChild(elCustomFieldSet);
                    }

                    document.body.appendChild(elEditForm);
                    jQuery(elEditForm).dialog({
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
                            "Save": () => {
                                jQuery(elEditForm).dialog('close');
                                document.body.removeChild(elEditForm);
                            },
                            "Cancel": () => {
                                jQuery(elEditForm).dialog('close');
                                document.body.removeChild(elEditForm);
                            }
                        },
                        open: function () {
                            // close dialog by clicking the overlay behind it
                            jQuery('.ui-widget-overlay').bind('click', function(){
                                jQuery(elEditForm).dialog('close');
                            });
                        },
                        create: function () {
                            // style fix for WordPress admin
                            jQuery('.ui-dialog-titlebar-close').addClass('ui-button');
                        }
                    });

                    jQuery(elEditForm).dialog('open');
                }
                else {
                    console.log('Something went wrong finding the randomonial!');
                }
            });
}