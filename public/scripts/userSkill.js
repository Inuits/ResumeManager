var skillCategoryData;

/**
 * Load skill data.
 */
var skillCategoryDataUpd = function(openWindow) {
    if (openWindow && skillCategoryData) {
        cvData['globalProfile']['skill']['form']('Edit skills');
    }
    else if (!openWindow) {
        $.get('/ajax/userskill/uid/' + userId, function(data) {
            skillCategoryData = Ext.decode(data);
        });
    }
    else if (openWindow) {
        Mask.show();
        $.get('/ajax/userskill/uid/' + userId, function(data) {
            Mask.hide();
            skillCategoryData = Ext.decode(data);
            cvData['globalProfile']['skill']['form']('Edit skills');
        });
    }
}

/**
 * Skill grid panel.
 */
var skillGridPanel = function(title) {
    var tableTitle = '';
    var skillListGridPanel = new Object();

    if (title) {
        var tableTitle = '<h3>' + title + '</h3>';
    }
    $('<div class="skill_list_table" id="skill_list">' + tableTitle + '</div>').appendTo('#user_profile');

    skillListGridPanel = {
        store: tables['skillStore'],
        xtype: 'actioncolumn',
        stateful: true,
        stateId: 'skillListGridj',
        id: 'skillListGrid',
        renderTo: 'skill_list',
        // hideHeaders: true,
        columns: [ {
            text: "Category",
            align: 'right',
            sortable: false,
            renderer: rendererTitle,
            width: 200,
            dataIndex: 'type'
        }, {
            text: "Skills",
            flex: 1,
            fixed: true,
            draggable: false,
            sortable: false,
            dataIndex: 'skill'
        } ]
    };

    // Top panel
    skillListGridPanel.dockedItems = [ {
        xtype: 'toolbar',
        items: [ {
            itemId: 'edit',
            text: 'Edit skills',
            iconCls: 'icon-edit',
            disabled: false,
            handler: function() {
                skillCategoryDataUpd(true);
            }
        } ]
    } ];

    return skillListGridPanel;
}

/**
 * Store data.
 */
cvData['globalProfile']['skill'] = {
    sorters: [],
    fields: [ {
        name: 'type',
        type: 'string'
    }, {
        name: 'skill',
        type: 'string'
    } ]
};

/**
 * Form.
 */
cvData['globalProfile']['skill']['form'] = function(title, tr) {
    var win;
    var tabItems = new Array();

    for ( var key in skillCategoryData) {
        var itemsVar = new Array();
        var lastKey = 0;
        var noSkillsText = '';
        var tabIcon = 'bullet_white';

        // Add skills.
        for ( var key2 in skillCategoryData[key]) {
            var checkedVar = false;
            lastKey = skillCategoryData[key][key2].id;
            if (skillCategoryData[key][key2].act == 1) {
                checkedVar = true;
                tabIcon = 'bullet_yellow';
            }
            if (skillCategoryData[key][key2].name) {
                itemsVar.push( {
                    boxLabel: skillCategoryData[key][key2].name,
                    name: lastKey,
                    width: 200,
                    checked: checkedVar
                });
            }
        }
        if (itemsVar.length == 0) {
            noSkillsText = 'No skills to select, please add new.';
        }
        
        // Add tabs.
        tabItems.push( {
            xtype: 'fieldcontainer',
            layout: 'anchor',
            title: key,
            enableTabScroll: true,
            iconCls: tabIcon,
            items: [ {
                xtype: 'fieldset',
                title: 'Select items',
                collapsed: false,
                items: [ {
                    value: noSkillsText,
                    xtype: 'displayfield',
                    anchor: '100%'
                }, {
                    title: key,
                    xtype: 'checkboxgroup',
                    columns:  4,
                    vertical: true,
                    flex: 1,
                    items: itemsVar
                } ]
            }, {
                xtype: 'fieldset',
                checkboxToggle: true,
                title: 'Add new',
                collapsed: true,
                checkboxName: 'new_' + lastKey,
                items: [ {
                    value: 'A comma-separated list of new skills. Example: PHP, MySQL, CSS.',
                    xtype: 'displayfield',
                    anchor: '100%'
                }, {
                    xtype: 'textfield',
                    anchor: '100%',
                    name: 'new_val_' + lastKey,
                    allowBlank: true
                } ]
            } ]
        });
    }
    
    // Add new category tab.
    tabItems.push( {
        xtype: 'fieldcontainer',
        layout: 'anchor',
        title: 'Add new category',
        enableTabScroll: true,
        iconCls: 'plus-small',
        items: [{
            value: 'Category name:',
            xtype: 'displayfield',
            anchor: '100%'
        }, {
            xtype: 'textfield',
            anchor: '100%',
            name: 'add_category',
            allowBlank: true
        }]
    });

    // Main window settings.
    var tabs = Ext.create('Ext.form.Panel', {
        width: 900,
        border: false,
        bodyBorder: false,
        defaults: {
            anchor: '100%'
        },
        layout: 'fit',
        items: {
            xtype: 'tabpanel',
            activeTab: 0,
            bodyPadding: 10,
            enableTabScroll: true,
            plugins: [{
                ptype: 'tabscrollermenu',
                maxText  : 35,
                pageSize : 20
            }],
            items: tabItems
        },
        buttons: [ {
            text: 'Cancel',
            handler: function() {
                this.up('window').hide();
            }
        }, {
            text: 'Save',
            handler: function() {
                if (this.up('form').getForm().isValid()) {
                    this.up('form').getForm().submit( {
                        clientValidation: true,
                        url: '/ajax/updateskill/uid/' + userId,
                        success: function(form, action) {
                            printMessages(action.result.messages);
                            tables['skillStore'].load();
                            skillCategoryDataUpd();
                        },
                        failure: function(form, action) {
                            printMessages(action.result.messages);
                            tables['skillStore'].load();
                            skillCategoryDataUpd();
                        }
                    });
                    this.up('window').hide();
                }
            }
        } ]
    });

    win = Ext.widget('window', {
        title: title,
        layout: 'fit',
        resizable: true,
        border: false,
        modal: true,
        items: tabs
    }).show();
}