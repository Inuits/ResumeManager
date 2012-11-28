var userListStore;
var userList;
var userListSkill = 0;

var userListGridPanel = function(title) {
    Ext.QuickTips.init();
    var tableTitle = '';
    var userListGridPanel = new Object();

    if (title) {
        var tableTitle = '<h2>' + title + '</h2>';
    }
    $('<div class="user_list_table" id="user_list">' + tableTitle + '</div>').appendTo('#content');
    

    
    // Grid
    var groupingFeature = Ext.create('Ext.grid.feature.Grouping', {
        groupHeaderTpl: 'Group: {name} ({rows.length} Item{[values.rows.length > 1 ? "s" : ""]})'
    });
    
    userListGridPanel = Ext.create('Ext.grid.Panel', {
        store: userListStore,
        region: 'center',
        title: 'All employees',
        split: true,
        //features: [groupingFeature],
        defaultSortable: true,
        xtype: 'actioncolumn',
        stateful: true,
        id: 'userListGridPanel',
        stateId: 'userListGridPanel-1',
        columns: [ {
            text: "Id",
            width: 30,
            hidden: true,
            dataIndex: 'id'
        }, {
            text: "Login",
            width: 150,
            hidden: true,
            dataIndex: 'userLogin'
        }, {
            text: "Level",
            hidden: true,
            dataIndex: 'userLevel'
        }, {
            text: "Name",
            width: 150,
            //            locked   : true,
            renderer: nameRenderer,
            dataIndex: 'lastName'
        }, {
            text: "First name",
            hidden: true,
            dataIndex: 'firstName'
        }, 
//        {
//            text: "Last name",
//            hidden: true,
//            dataIndex: 'lastName'
//        }, 
        {
            text: "Location",
            dataIndex: 'location'
        }, {
            text: "Date of Birth",
            width: 150,
            dataIndex: 'birthDate'
        }, {
            text: "Birth place",
            hidden: true,
            dataIndex: 'birthPlace'
        }, {
            text: "Security",
            hidden: true,
            dataIndex: 'socialSecurity'
        }, {
            text: "Language",
            hidden: true,
            dataIndex: 'lang'
        }, {
            text: "Nationality",
            hidden: true,
            dataIndex: 'nationality'
        }, {
            text: "Company",
            flex: 1,
            dataIndex: 'company'
        }, {
            text: "Profile",
            hidden: true,
            dataIndex: 'profile'
        } ]
    });
    
    
    var skillsSore = Ext.create('Ext.data.TreeStore', {
        proxy: {
            type: 'ajax',
            url: '/ajax/userskilltree'
        },
        sorters: [{
            property: 'text',
            direction: 'ASC'
        }]
    });
    var skillsTree = Ext.create('Ext.tree.Panel', {
        region: 'west',
        title: 'Skills',
        width: 250,
        stateId: 'statePanelSkills',
        split: true,
        collapsible: true,
        store: skillsSore,
        rootVisible: false,
        useArrows: true,
        dockedItems: [{
            xtype: 'toolbar',
            items: [{
                text: 'Expand All',
                handler: function(){
                    skillsTree.expandAll();
                }
            }, {
                text: 'Collapse All',
                handler: function(){
                    skillsTree.collapseAll();
                }
            }]
        }]
    });
    
    skillsTree.getSelectionModel().on('selectionchange', function(selModel, selections){
        if (selections.length == 0) {
            return ;
        }
        if (selections[0].data.id < 9000) {
            userListSkill = selections[0].data.id;
            userListGridPanel.setTitle('Employees with skill: ' + selections[0].data.text);
            userListStore.load({
                type: 'ajax',
                url: '/ajax/userlist/skill/' + selections[0].data.id,
                reader: {
                    type: 'json',
                    root: 'data'
                }
            });
        }
        else if (userListSkill !== 0) {
            userListSkill = 0;
            userListGridPanel.setTitle('All employees');
            userListStore.load({
                type: 'ajax',
                url: '/ajax/userlist',
                reader: {
                    type: 'json',
                    root: 'data'
                }
            });
        }
    });
    
    
    Ext.create('Ext.panel.Panel', {
        layout: 'border',
        renderTo: 'user_list',
        height: 600,
        items: [skillsTree, userListGridPanel]
    });
    
    return userListGridPanel;
}

/**
 * Store
 */
var userListStoreData = {
    autoLoad: true,
//    groupField: 'location',
    fields: [ {
        name: 'id',
        type: 'int',
        useNull: true
    }
    , {
        name: 'userLogin',
        type: 'string'
    }, {
        name: 'userLevel',
        type: 'string'
    }, {
        name: 'firstName',
        type: 'string'
    }, {
        name: 'lastName',
        type: 'string'
    }, {
        name: 'location',
        type: 'string'
    }, {
        name: 'birthDate',
        type: 'string'
    }, {
        name: 'birthPlace',
        type: 'string'
    }, {
        name: 'socialSecurity',
        type: 'string'
    }, {
        name: 'lang',
        type: 'string'
    }, {
        name: 'nationality',
        type: 'string'
    }, {
        name: 'company',
        type: 'string'
    }, {
        name: 'viewLink',
        type: 'string'
    }
    ],

    sorters: [ {
        property: 'lastName',
        direction: 'ASC'
    } ],

    proxy: {
        type: 'ajax',
        url: '/ajax/userlist',
        reader: {
            type: 'json',
            root: 'data'
        }
    }
};
