/**
 * Axis
 *
 * This file is part of Axis.
 *
 * Axis is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Axis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Axis.  If not, see <http://www.gnu.org/licenses/>.
 */

Ext.onReady(function() {

    var tabs = Ext.query("#axis-tabs a");

    for (var i=0; i<tabs.length; i++){
        tabs[i].onclick = new Function("clickTab(\'" + tabs[i].id + "\'); return false;");
    }

    $('#createButton').unbind().bind('click', function(){
        createGroup();
        return false;
    });

    if (groupId == '') {
        Ext.getDom('deleteButton').style.display = 'none';
        createGroup();
    } else {
        loadGroupData();
    }
})


function clickTab(tabId){
    if (tabId == 'new') {
        return;
    }else if (editing_new_group){
        alert ('Save your New group first');
        return;
    }
    //checking where click was. If on active tab - return;
    if (tabId == 'tab-' + groupId){
        return;
    }
    //activating clicked tab
    var elem = document.getElementById('axis-tabs').getElementsByTagName('a');
    for (var i = 0; i < elem.length; i++) {
        elem[i].className = "";
    }
    document.getElementById(tabId).className = "active";
    groupId = tabId.replace(/tab-/, '');
    //reloading grid
    ds.proxy.conn.url = Axis.getUrl('account/field/get-fields/groupId/' + groupId + '/');
    ds.load();
    loadGroupData();
    return false;
}

function loadGroupData(){
    //reloading groupInfo tabs
    Ext.Ajax.request({
        url: Axis.getUrl('account/field/get-group-info/groupId/') + groupId,
        success: function(response){
            var data = eval('('+ response.responseText +')');
            Ext.getDom('name').value = data.data[0] ? data.data[0].name : '';
            Ext.getDom('sort_order').value = data.data[0] ? data.data[0].sort_order : '';
            Ext.getDom('is_active').value = data.data[0] ? data.data[0].is_active : '';
            for (var i=0, length = data.data.length; i < length; i++){
                Ext.getDom('group_label-' + data.data[i].language_id).value = data.data[i].group_label;
            }
        },
        failure: function(){
            loadGroupData();
        }
    });
}

function createGroup() {
    //if new unsaved group exist:
    if (editing_new_group){
        alert ('Save your New group first');
        return;
    }

    temp_groupId = groupId;
    groupId = null;
    editing_new_group = true;

    Ext.query('span', 'deleteButton')[0].firstChild.nodeValue = 'Cancel';
    Ext.query('span', 'createButton')[0].firstChild.nodeValue = 'Save';
    $('#createButton').unbind().bind('click', function(){
        saveGroup();
        return false;
    });

    var li = document.createElement('li');
    var a = document.createElement('a');
    var title = document.createTextNode('New group');
    a.href = '#';

    if (Ext.query('.active', '#axis-tabs')[0]){
        Ext.query('.active', '#axis-tabs')[0].className = '';
    }

    a.className = 'active';
    a.appendChild(title);
    li.appendChild(a);
    document.getElementById('axis-tabs').appendChild(li);

    Ext.query('.active', '#axis-tabs')[0].id = 'new';
    $('#axis-tabs .active').bind('click', function(){
        clickTab(this.id);
        return false;
    });
    //hide grid
    Ext.getDom('fields-grid').style.display = 'none';

    //reset language tabs
    var el = Ext.query('input', 'language-tabs');
    for (var i = 0; i < el.length; i++) {
        el[i].value = '';
    }
    Ext.getDom('sort_order').value = '3';
}

function saveGroup(){
    var data = {};

    var el = Ext.query('input', 'language-tabs');
    for (var i = 0; i < el.length; i++) {
       data[el[i].id] = el[i].value;
    }
    data['id'] = groupId;
    data['is_active'] = Ext.getDom('is_active').value;

    var jsonData = Ext.encode(data);
    if (data['name'] == ''){
        alert('enter valid Group name');
        return;
    }
    Ext.Ajax.request({
        url: Axis.getUrl('account/field/ajax-save-group'),
        params: {data: jsonData},
        success: function(response){
            if (editing_new_group){
                editing_new_group = false;
                //get id of new group from response
                var new_group = eval('('+ response.responseText +')');
                groupId = new_group.group_id;

                Ext.query('span', 'deleteButton')[0].firstChild.nodeValue = 'Delete group';
                //show grid
                Ext.getDom('fields-grid').style.display = 'block';
                //set tab id to tab-groupId
                Ext.getDom('new').id='tab-' + groupId;
            }

            //changing tab text
            var active = Ext.query('.active', '#axis-tabs');
            active[0].firstChild.nodeValue = data['group_label-' + Axis.language];
            //reloading grid
            gs.reload();
            ds.baseParams.groupId = groupId;
            ds.load();

            //getting createButton back
            Ext.query('span', 'createButton')[0].firstChild.nodeValue = 'Create group';
            $('#createButton').unbind().bind('click', function(){
                createGroup();
                return false;
            });
            Ext.getDom('deleteButton').style.display = 'block';
        }
    });
}

function deleteGroup() {
    if (editing_new_group){             //if new group not saved yet (other groups are exist)
        groupId = temp_groupId;
        Ext.get('new').parent().remove();
        editing_new_group = false;
        Ext.query('span', 'deleteButton')[0].firstChild.nodeValue = 'Delete group';
        Ext.query('span', 'createButton')[0].firstChild.nodeValue = 'Create group';
        $('#createButton').unbind().bind('click', function(){
            createGroup();
            return false;
        });
        //switching to previous active group
        Ext.getDom('fields-grid').style.display = 'block';
        Ext.getDom('tab-' + groupId ).className = 'active';
        ds.proxy.conn.url = Axis.getUrl('account/field/get-fields/groupId/' + groupId + '/');
        ds.reload();
        loadGroupData();

    } else { //if deleting existing and saved group
        if (!groupId) { //if groups not exist yet
            return;
        }
        if (!confirm('Are you sure?'.l())) {
            return false;
        }
        Ext.Ajax.request({
            url: Axis.getUrl('account/field/ajax-delete-group'),
            params: {id: groupId},
            callback: function() {
                Ext.get('tab-' + groupId).parent().remove();

                Ext.Ajax.request({
                    url: Axis.getUrl('account/field/get-groups'),
                    success: function(response) {
                        var response = eval('('+ response.responseText +')');

                        for (var i in response.data) {
                            groupId = response.data[i].id;
                            Ext.getDom('tab-' + groupId).className = 'active';
                            ds.baseParams.groupId = groupId;
                            ds.reload();
                            loadGroupData();
                            return;
                        }

                        createGroup();
                        Ext.getDom('deleteButton').style.display = 'none';
                    }
                });
            }
        });
    }
}