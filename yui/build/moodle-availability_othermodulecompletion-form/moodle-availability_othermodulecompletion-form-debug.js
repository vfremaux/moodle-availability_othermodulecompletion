YUI.add('moodle-availability_othermodulecompletion-form', function (Y, NAME) {

/*
 * JavaScript for form editing othermodulecompletion conditions.
 *
 * @module moodle-availability_othermodulecompletion-form
 */
// jshint unused:false, undef:false

M.availability_othermodulecompletion = M.availability_othermodulecompletion || {};

/*
 * @class M.availability_othermodulecompletion.form
 * @extends M.core_availability.plugin
 */
M.availability_othermodulecompletion.form = Y.Object(M.core_availability.plugin);

/*
 * Groupings available for selection (alphabetical order).
 *
 * @property days
 * @type Array
 */
M.availability_othermodulecompletion.form.cmidnumber = null;

/*
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} standardFields Array of objects with .field, .display
 * @param {Array} customFields Array of objects with .field, .display
 */
M.availability_othermodulecompletion.form.initInner = function(cmidnumber) {
    this.cmidnumber = cmidnumber;
};

M.availability_othermodulecompletion.form.getNode = function(json) {
    // Create HTML structure.
    var strings = M.str.availability_othermodulecompletion;

    if (json.cmid === undefined) {
        json.cmid = '';
    }

    var html = '<span class="availability-group"><label>' + strings.conditiontitle;
    html += ' <input type="text" size="16" name="field" value="' + json.cmid + '"></label></span>';

    var node = Y.Node.create('<span>' + html + '</span>');

    // Set initial values if specified.
    if (json.cmid !== undefined) {
        node.one('input[name=field]').set('value', json.cmid);
    }

    // Add event handlers (first time only).
    if (!M.availability_othermodulecompletion.form.addedEvents) {
        M.availability_othermodulecompletion.form.addedEvents = true;
        var updateForm = function(input) {
            var ancestorNode = input.ancestor('span.availability_othermodulecompletion');
            M.core_availability.form.update();
        };

        var root = Y.one('.availability-field');
        root.delegate('change', function() {
             updateForm(this);
        }, '.availability_othermodulecompletion input[name=field]');
    }

    return node;
};

M.availability_othermodulecompletion.form.fillValue = function(value, node) {
    // Set field.
    value.cmid = node.one('input[name=field]').get('value');
};

}, '@VERSION@', {"requires": ["base", "node", "event", "io", "moodle-core_availability-form"]});
