/**
 * Modified version of Ext.ux.maximgb.treegrid.NestedSetStore
 * Added rootFiledName property to support multiple root count
 */
Axis.data.NestedSetStore = Ext.extend(Ext.ux.maximgb.tg.NestedSetStore, {

    left_field_name: 'lft',

    level_field_name: 'lvl',

    right_field_name: 'rgt',

    rootFieldName: 'root',

    root_node_level: 0,

    /**
     * Added check for rootFieldName
     */
    getNodeChildren : function(rc) {
        var lft, r_lft,
            rgt, r_rgt,
            level, r_level,
            rootField, r_rootField,
            records, rec,
            result = [];

        records = this.data.getRange();

        lft = rc.get(this.left_field_name);
        rgt = rc.get(this.right_field_name);
        level = rc.get(this.level_field_name);
        rootField = rc.get(this.rootFieldName);

        for (i = 0, len = records.length; i < len; i++) {
            rec = records[i];
            r_lft = rec.get(this.left_field_name);
            r_rgt = rec.get(this.right_field_name);
            r_level = rec.get(this.level_field_name);
            r_rootField = rec.get(this.rootFieldName);

            if (
                r_level == level + 1 &&
                rootField == r_rootField &&
                r_lft > lft &&
                r_rgt < rgt
            ) {
                result.push(rec);
            }
        }

        return result;
    },

    // getNodeDepth: function(rc) {
        // return rc.get(this.levelFieldName) - this.rootNodeLevel;
    // },


    /**
     * Check for rootFieldName added
     */
    getNodeParent: function(rc) {
        var result = null,
            rec, records = this.data.getRange(),
            i, len,
            lft, r_lft,
            rgt, r_rgt,
            level, r_level,
            rootField, r_rootField;

        lft = rc.get(this.left_field_name);
        rgt = rc.get(this.right_field_name);
        level = rc.get(this.level_field_name);
        rootField = rc.get(this.rootFieldName);

        for (i = 0, len = records.length; i < len; i++) {
            rec = records[i];
            r_lft = rec.get(this.left_field_name);
            r_rgt = rec.get(this.right_field_name);
            r_level = rec.get(this.level_field_name);
            r_rootField = rec.get(this.rootFieldName);

            if (
                r_level == level - 1 &&
                rootField == r_rootField &&
                r_lft < lft &&
                r_rgt > rgt
            ) {
                result = rec;
                break;
            }
        }

        return result;
    },
//
    // getRootNodes: function() {
        // var i,
                // len,
                // result = [],
                // records = this.data.getRange();
//
        // for (i = 0, len = records.length; i < len; i++) {
            // if (records[i].get(this.levelFieldName) == this.rootNodeLevel) {
                // result.push(records[i]);
            // }
        // }
//
        // return result;
    // },

    isLeafNode: function(rc) {
        return !this.hasChildNodes(rc);// rc.get(this.rightFieldName) - rc.get(this.leftFieldName) <= 1;
    }
});
