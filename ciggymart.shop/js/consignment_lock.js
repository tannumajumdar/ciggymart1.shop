/*
 * Field lock for the consignment booking form.
 *
 * Every input marked data-lockable gets a lock icon next to its label.
 * A locked field keeps its value for the next booking (saved in the
 * browser, so it also survives a page reload) and can't be edited until
 * it is unlocked. A locked AWB number moves on to the next number after
 * each save, so a series of AWBs can be booked back to back.
 */
var FieldLock = (function ($) {
    var storageKey = 'consignment_locks';
    var locks = {};

    function read() {
        try {
            var raw = window.localStorage.getItem(storageKey);
            return raw ? JSON.parse(raw) : {};
        } catch (e) {
            return {};
        }
    }

    function write() {
        try {
            window.localStorage.setItem(storageKey, JSON.stringify(locks));
        } catch (e) { /* storage blocked: locks last for this page only */ }
    }

    function field(name) {
        return $('[data-lockable][name="' + name + '"]');
    }

    function paint(name, isLocked) {
        var $f = field(name);
        $f.toggleClass('is-locked', isLocked);
        if ($f.is('select')) {
            $f.attr('tabindex', isLocked ? '-1' : null);
        } else {
            $f.prop('readonly', isLocked);
        }
        $('.fld-lock[data-name="' + name + '"]')
            .toggleClass('on', isLocked)
            .attr('title', isLocked ? 'Locked - value is kept for the next booking. Click to unlock.' : 'Lock this value for the next booking')
            .find('i').attr('class', isLocked ? 'fa fa-lock' : 'fa fa-unlock-alt');
    }

    // "KE1002345" -> "KE1002346"; keeps leading zeros. No trailing digits -> unchanged.
    function nextAwb(awb) {
        var m = String(awb).match(/^(.*?)(\d+)$/);
        if (!m) return awb;
        var n = String(parseInt(m[2], 10) + 1);
        while (n.length < m[2].length) n = '0' + n;
        return m[1] + n;
    }

    function setValue(name, value) {
        var $f = field(name);
        $f.val(value);
        $f.trigger($f.is('select') ? 'change' : 'input');
    }

    return {
        init: function (key) {
            storageKey = key || storageKey;
            locks = read();

            $('[data-lockable]').each(function () {
                var name = $(this).attr('name');
                var $label = $(this).closest('.form-group, .lock-group').find('label').first();
                $('<a href="#" class="fld-lock" tabindex="-1"><i class="fa fa-unlock-alt"></i></a>')
                    .attr('data-name', name)
                    .appendTo($label);
            });

            $(document).on('click', '.fld-lock', function (e) {
                e.preventDefault();
                var name = $(this).data('name');
                if (locks.hasOwnProperty(name)) {
                    delete locks[name];
                    paint(name, false);
                } else {
                    locks[name] = field(name).val();
                    paint(name, true);
                }
                write();
            });

            this.restore(false);
        },

        isLocked: function (name) {
            return locks.hasOwnProperty(name);
        },

        // Put locked values back, e.g. after the form is reset for the next booking.
        restore: function (advanceAwb) {
            if (advanceAwb && locks.hasOwnProperty('consignment_no')) {
                locks.consignment_no = nextAwb(locks.consignment_no);
                write();
            }
            // Selects first, so client/destination handlers run before the fields they fill.
            var names = Object.keys(locks).sort(function (a, b) {
                return (field(b).is('select') ? 1 : 0) - (field(a).is('select') ? 1 : 0);
            });
            $.each(names, function (i, name) {
                if (!field(name).length) {
                    delete locks[name];
                    return;
                }
                setValue(name, locks[name]);
                paint(name, true);
            });
            write();
        }
    };
})(jQuery);
