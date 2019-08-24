function EditFormBuilder() {
    Object.defineProperty(this, 'selfClosing', 
    {
        value: ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'],
        writable: false,
        enumerable: true,
        configurable: false
    });

    // Core field container
    this.elCoreFieldset = document.createElement('fieldset');
    this.elCoreFieldset.id = 'randomonials_core_inputs';

    let elCoreLegend = document.createElement('legend');
    elCoreLegend.innerHTML = 'Core Fields';
    this.elCoreFieldset.appendChild(elCoreLegend);

    this.elCoreFieldContainer = document.createElement('div');
    this.elCoreFieldContainer.classList.add('randomonials-vbox');
    this.elCoreFieldset.appendChild(this.elCoreFieldContainer);

    this.elAuthorInputLabel = document.createElement('label');
    this.elAuthorInputLabel.setAttribute('for', 'randmonial_author_ipt');
    this.elAuthorInputLabel.innerText = 'Author:';
    this.elCoreFieldContainer.appendChild(this.elAuthorInputLabel);

    this.elAuthorInput = document.createElement('input');
    this.elAuthorInput.id = 'randmonial_author_ipt';
    this.elAuthorInput.required = true;
    this.elAuthorInput.setAttribute('type', 'text');
    this.elAuthorInput.setAttribute('name', 'author');
    this.elAuthorInput.setAttribute('minlength', '1');
    this.elAuthorInput.setAttribute('maxlength', '100');
    this.elAuthorInput.setAttribute('placeholder', 'Author goes here...');
    this.elCoreFieldContainer.appendChild(this.elAuthorInput);

    this.elCommentInputLabel = document.createElement('label');
    this.elCommentInputLabel.setAttribute('for', 'randmonial_comment_ipt');
    this.elCommentInputLabel.innerText = 'Comment:';
    this.elCoreFieldContainer.appendChild(this.elCommentInputLabel);

    this.elCommentTextarea = document.createElement('textarea');
    this.elCommentTextarea.id = 'randmonial_comment_ipt';
    this.elCommentTextarea.required = true;
    this.elCommentTextarea.setAttribute('name', 'comment');
    this.elCommentTextarea.setAttribute('rows', '10');
    this.elCommentTextarea.setAttribute('cols', '60');
    this.elCommentTextarea.setAttribute('placeholder', 'Comment goes here...');
    this.elCoreFieldContainer.appendChild(this.elCommentTextarea);

    // Declare Custom Fieldset
    this.elCustomFieldset = null;
}

EditFormBuilder.prototype.getAttributeControls = function(_attributes) {
    let aAttrSpans = [];

    if (Array.isArray(_attributes)) {
        _attributes.forEach(attribute => 
            {
                let iEqual = attribute.indexOf('=');
                let sAttr = attribute.substring(0, iEqual);
                let sAttrVal = attribute.substring((iEqual+1));

                let elButton = document.createElement('button');
                elButton.setAttribute('onclick', 'set_tag_param(this);');
                elButton.setAttribute('type', 'button');
                elButton.dataset.fieldParam = sAttr;

                if (sAttrVal.length > 0) {
                    elButton.classList.add('randomonial-tag-html-set');
                    elButton.innerHTML = 'Modify';
                } 
                else {
                    elButton.innerHTML = 'Set';
                }
                
                let elAttrSpan = document.createElement('span');
                elAttrSpan.appendChild(document.createTextNode(sAttr + '="'));
                elAttrSpan.appendChild(elButton);
                elAttrSpan.appendChild(document.createTextNode('"'));

                aAttrSpans.push(elAttrSpan);
            });
    }

    return aAttrSpans;
};

EditFormBuilder.prototype.capitalize = function(str) {
    return str.replace(/\w\S*/g, function(txt){
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
};

EditFormBuilder.prototype.setAuthor = function(_author) {
    this.elAuthorInput.value = _author;
};

EditFormBuilder.prototype.setComment = function(_comment) {
    this.elCommentTextarea.value = _comment;
};

EditFormBuilder.prototype.addCustomField = function(_name, _template, _randomonial) {
    if (this.elCustomFieldset == null) {
        // Custom field container
        this.elCustomFieldset = null;
        this.elCustomFieldset = document.createElement('fieldset');
        this.elCustomFieldset.id = 'randomonials_custom_inputs';

        let elCustomLegend = document.createElement('legend');
        elCustomLegend.innerHTML = 'Custom HTML Fields';
        this.elCustomFieldset.appendChild(elCustomLegend);
    }

    let elCustomFieldTitle = document.createElement('div');
    elCustomFieldTitle.classList.add('randomonials-tag-title');
    elCustomFieldTitle.innerText = this.capitalize(_name) + ' Field';

    let elCustomFieldHTML = document.createElement('div');
    elCustomFieldHTML.classList.add('randomonials-tag-html');
    elCustomFieldHTML.dataset.fieldName = _name;

    let sTagClass = '';
    let elOpenTag = null;
    let elCloseTag = null;

    if (_template['class'].length > 0) {
        sTagClass = ' class="' + _template['class'] + '"';
    }

    if (this.selfClosing.indexOf(_template['type']) > -1) {
        if (_randomonial['attributes'].length > 0) {
            let aAttrCtrls = this.getAttributeControls(_randomonial['attributes']);

            elOpenTag = document.createElement('span');
            elOpenTag.appendChild(document.createTextNode('<' + _template['type'] + sTagClass + ' '));

            for (let i=0;i<aAttrCtrls.length;i++) {
                elOpenTag.appendChild(aAttrCtrls[i]);
            }

            elCloseTag = document.createElement('span');
            elCloseTag.appendChild(document.createTextNode('>'));
            
            elCustomFieldHTML.appendChild(elOpenTag);
            elCustomFieldHTML.appendChild(elCloseTag);
        }
        else {
            elOpenTag = document.createElement('span');
            elOpenTag.appendChild(document.createTextNode('<' + _template['type'] + sTagClass + '>'));
            
            elCustomFieldHTML.appendChild(elOpenTag);
        }
    }
    else {
        if (_randomonial['attributes'].length > 0) {
            let aAttrCtrls = this.getAttributeControls(_randomonial['attributes']);

            elOpenTag = document.createElement('span');
            elOpenTag.appendChild(document.createTextNode('<' + _template['type'] + sTagClass + ' '));

            for (let i=0;i<aAttrCtrls.length;i++) {
                elOpenTag.appendChild(aAttrCtrls[i]);
            }

            elCloseTag = document.createElement('span');
            elCloseTag.appendChild(document.createTextNode('</' + _template['type'] + '>'));
        }
        else {
            elOpenTag = document.createElement('span');
            elOpenTag.appendChild(document.createTextNode('<' + _template['type'] + sTagClass + '>'));

            elCloseTag = document.createElement('span');
            elCloseTag.appendChild(document.createTextNode('</' + _template['type'] + '>'));
        }

        elFieldValue = document.createElement('button');
        elFieldValue.setAttribute('type', 'button');
        elFieldValue.dataset.fieldParam = 'value';

        if (_randomonial['value'].length > 0) {
            elFieldValue.classList.add('randomonial-tag-html-set');
            elFieldValue.innerHTML = 'Modify';
        }
        else {
            elFieldValue.innerHTML = 'Set';
        }

        elCustomFieldHTML.appendChild(elOpenTag);
        elCustomFieldHTML.appendChild(elFieldValue);
        elCustomFieldHTML.appendChild(elCloseTag);
    }

    let elCustomFieldContainer = document.createElement('div');
    elCustomFieldContainer.classList.add('randomonials-custom-tag-container');
    elCustomFieldContainer.appendChild(elCustomFieldTitle);
    elCustomFieldContainer.appendChild(elCustomFieldHTML);

    this.elCustomFieldset.appendChild(elCustomFieldContainer);
};

EditFormBuilder.prototype.getCoreFieldset = function() {
    return this.elCoreFieldset;
};

EditFormBuilder.prototype.getCustomFieldset = function() {
    return this.elCustomFieldset;
};