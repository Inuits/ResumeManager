cvData['globalProfile']['publicity'] = {
    sorters: [ {
        property: 'date',
        direction: 'DESC'
    } ],
    fields: [ {
        name: 'id',
        type: 'int',
        useNull: true
    }, {
        name: 'date',
        type: 'date',
        dateFormat: 'Y-m-d'
    }, {
        name: 'title',
        type: 'string'
    }, {
        name: 'link',
        type: 'string'
    }, {
        name: 'description',
        type: 'string'
    } ],

    columns: [ {
        text: "Id",
        width: 30,
        hidden: true,
        dataIndex: 'id'
    }, {
        text: "Title",
        width: 150,
        renderer: rendererTitle2,
        dataIndex: 'title',
        field: {
            xtype: 'textfield'
        }
    }, {
        text: "Date",
        width: 100,
        dataIndex: 'date',
        renderer: dateFormat,
        field: {
            xtype: 'datefield',
            format: 'Y-m-d'
        }
    }, {
        text: "Link",
        width: 100,
        dataIndex: 'link',
        sortable: false,
        renderer: linkFormat,
        field: {
            xtype: 'textfield'
        }
    }, {
        text: "Description",
        flex: 1,
        fixed: true,
        draggable: false,
        sortable: false,
        dataIndex: 'description',
        field: {
            height: 40,
            xtype: 'textareafield'
        }
    } ]
};

cvData['globalProfile']['publication'] = cvData['globalProfile']['publicity'];
cvData['globalProfile']['talk'] = cvData['globalProfile']['publicity'];

/**
 * Form
 */
cvData['globalProfile']['publicity']['form'] = function(title, key, tr) {
    var tabItems = new Array();
    tabItems[0] = publicityFormFields(false, 'Original (en)');
    for ( var langKey in langs) {
        tabItems.push(publicityFormFields(langKey, langs[langKey]));
    }

    var tabs = Ext.create('Ext.tab.Panel', {
        resizeTabs: true,
        enableTabScroll: true,
        defaults: {
            closable: false
        },
        items: tabItems
    });

    var form = Ext.widget('form', {
        items: tabs,
        buttons: [ {
            text: 'Cancel',
            handler: function() {
                this.up('form').getForm().reset();
                this.up('window').hide();
            }
        }, {
            text: 'Save',
            handler: function() {
                if (this.up('form').getForm().isValid()) {
                    this.up('form').getForm().submit( {
                        clientValidation: true,
                        url: '/ajax/save/uid/' + userId + '/type/' + key,
                        success: function(form, action) {
                            printMessages(action.result.messages);
                            tables[key + 'Store'].load();
                        },
                        failure: function(form, action) {
                            printMessages(action.result.messages);
                            tables[key + 'Store'].load();
                        }
                    });
                    this.up('window').hide();
                }
            }
        } ]
    });
    
    if (tr) {
        form.loadRecord(tr);
    }

    Ext.widget('window', {
        title: title,
        width: 500,
        layout: 'anchor',
        resizable: false,
        border: false,
        modal: true,
        items: form,
        listeners: {
            afterrender: function() {
                if (tr) {
                    form.getForm().load( {
                        url: '/ajax/langload/id/' + tr.get('id') + '/type/' + key,
                        failure: function(form, action) {
                            Ext.Msg.alert("Load failed", action.result.errorMessage);
                        }
                    });
                }
            }
        }
    }).show();
}



var publicityFormFields = function(lang, title) {
    var translate = false;
    if (lang) {
        lang = lang + ':';
        translate = true;
    } else {
        lang = '';
    }
    var items_data = [];

    if (translate) {
        items_data.push(enableLangCheckbox(lang, title));
    }

    items_data.push({
        fieldLabel: 'Title',
        name: lang + 'title',
        xtype: 'textfield',
        anchor: '100%',
        allowBlank: false,
        disabled: translate,
        labelAlign: 'right',
        labelStyle: 'font-weight:normal',
        labelWidth: 70
    });
    
    if (!translate) {
        items_data.push({
            fieldLabel: 'Date',
            name: 'date',
            xtype: 'datefield',
            format: 'Y-m-d',
            id: 'date',
            anchor: '50%',
            allowBlank: false,
            labelAlign: 'right',
            labelStyle: 'font-weight:normal',
            labelWidth: 70
        });
        items_data.push({
            fieldLabel: 'Link',
            name: 'link',
            vtype: 'url',
            anchor: '100%',
            allowBlank: false,
            labelAlign: 'right',
            labelStyle: 'font-weight:normal',
            labelWidth: 70
        });
    }
    
    items_data.push( {
        name: lang + 'description',
        xtype: 'textareafield',
        disabled: translate,
        anchor: '100%',
        height: 100
    });

    if (!translate) {
        items_data.push( {
            xtype: 'hidden',
            name: 'id'
        });
    }

    return formFields = {
        title: translate ? title + ' [-]' : title,
        layout: 'anchor',
        border: false,
        defaultType: 'textfield',
        bodyPadding: 10,
        frame: true,
        fieldDefaults: {
            msgTarget: 'qtip'
        },
        defaults: {
            msgTarget: 'side',
            margins: '0 0 10 0'
        },
        items: items_data
    };
}