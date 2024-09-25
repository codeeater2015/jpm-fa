var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var KamadaPrelistingModal = React.createClass({
    getInitialState: function () {
        return {
            proIdCode: null,
            member: null,
            showAttendeeModal: false,
            showAttendeeBatchModal: false,
            events: [],
            selectedEvent: null
        }
    },

    render: function () {
        var self = this;
        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} dialogClassName="modal-custom-85" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Prelisting</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <button onClick={this.openAttendeeModal} type="button" className="btn btn-sm btn-primary">Add Attendees</button>
                     {
                        this.state.showAttendeeModal &&
                        <KamadaPrelistingAddAttendeeModal
                            id={this.props.id}
                            show={this.state.showAttendeeModal}
                            notify={this.props.notify}
                            onSuccess={this.reloadDatatable}
                            onHide={this.closeAttendeeModal}
                        />
                    }


                    <KamadaPrelistingDetailDatatable ref="DetailDatatable" proId={this.props.proId} notify={this.props.notify} id={this.props.id}/>
                </Modal.Body>
            </Modal>
        );
    },


    componentDidMount: function () {
        this.loadEvents();
    },

    setFormProp: function (e) {
        this.setState({ proIdCode: e.target.value }, this.search);
    },

    search: function () {
        var self = this;
        var proIdCode = this.state.proIdCode;

        if (proIdCode != null && proIdCode != "") {
            setTimeout(function () {
                self.requestMember = $.ajax({
                    url: Routing.generate("ajax_get_project_voter_alt", { proIdCode: proIdCode, proId: self.props.proId }),
                    type: "GET"
                }).done(function (res) {

                    if (res.status == 'A') {
                        self.setState({ member: res }, self.add);
                    } else {
                        alert("Opps! Cant add to the list of attendees. Voter either blocked or deactivated");
                    }
                }).fail(function () {
                    console.log("member not found");
                    self.setState({ member: null });
                });
            }, 2000);
        }
    },

    loadEvents: function () {
        var self = this;

        self.requestEvents = $.ajax({
            url: Routing.generate("ajax_get_project_event_headers"),
            type: "GET"
        }).done(function (res) {
            console.log("events has been received");
            console.log(res);
            self.setState({ events: res });
        });
    },

    appendEventMembers: function () {
        var self = this;

        console.log("event id");
        console.log(self.state.selectedEvent);

        self.appendMembers = $.ajax({
            url: Routing.generate("ajax_post_project_event_header_append"),
            data: {
                eventId: self.state.selectedEvent,
                currentEventId: self.props.eventId
            },
            type: "POST"
        }).done(function (res) {
            console.log("members has been added");
            self.refs.DetailDatatable.reload();
            self.setState({ selectedEvent: null });
        }).fail(function () {
            console.log('failed to append members');
        });
    },

    setSelectedEvent: function (e) {
        this.setState({ selectedEvent: e.target.value });
    },

    add: function () {
        var self = this;

        self.requestMember = $.ajax({
            url: Routing.generate("ajax_post_project_event_detail"),
            data: {
                proVoterId: this.state.member.pro_voter_id,
                proId: this.props.proId,
                proIdCode: this.state.member.pro_id_code,
                eventId: this.props.eventId
            },
            type: "POST"
        }).done(function (res) {
            self.refs.DetailDatatable.reload();
            self.setState({ proIdCode: "" });
        }).fail(function () {
            self.setState({ proIdCode: "" });
        });
    },

    reloadDatatable: function () {
        this.refs.DetailDatatable.reload();
    },

    reloadFilteredDatatable: function (precinctNo) {
        this.refs.DetailDatatable.reloadFiltered(precinctNo);
    },

    openAttendeeModal: function () {
        this.setState({ showAttendeeModal: true });
    },

    closeAttendeeModal: function () {
        this.setState({ showAttendeeModal: false });
    },

    openAttendeeBatchModal: function () {
        this.setState({ showAttendeeBatchModal: true });
    },

    closeAttendeeBatchModal: function () {
        this.setState({ showAttendeeBatchModal: false });
    },

    showAttendaceSummary: function () {
        console.log("showing attendance summary");
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/kfc/attendance-summary/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'Attendance Summary', 900, 600);
    },

    showAllAttendees: function () {
        var url = "http://" + window.hostIp + ":83/jpm/event-attendance/?event_id=" + this.props.eventId;
        this.popupCenter(url, 'List of All Attendees', 900, 600);
    },


    showAllAttendeesByPosition: function () {
        var url = "http://" + window.hostIp + ":83/jpm/event-attendance-by-position/?event_id=" + this.props.eventId;
        this.popupCenter(url, 'List of All Attendees', 900, 600);
    },

    showAllAttendeesByBarangay: function () {
        var url = "http://" + window.hostIp + ":83/jpm/event-attendance-by-barangay/?event_id=" + this.props.eventId;
        this.popupCenter(url, 'List of All Attendees', 900, 600);
    },

    showNewPrintout: function () {
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/attendance-new/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'List of New Attendees', 900, 600);
    },

    showNewAllPrintout: function () {
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/attendance-new-all/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'List of All New Expecteed Attendees', 900, 600);
    },

    showNewByBarangayPrintout: function () {
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/attendance-new-by-barangay/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'List of New Attendees by Barangay', 900, 600);
    },

    showNewByAssignedPrecinctPrintout: function () {
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/attendance-new-by-assigned-precinct/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'List of New Attendees by Assigned Precinct', 900, 600);
    },

    showNewAllByAssignedPrecinctPrintout: function () {
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/attendance-new-all-by-assigned-precinct/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'List of All New Expecteed Attendees By Assigned Precinct', 900, 600);
    },

    showStabs: function () {
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/voter-stab/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'Stabs', 900, 600);
    },

    showStabsByBarangay: function () {
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/voter-stab-by-barangay/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'Stabs', 900, 600);
    },


    showStabsByPrecinct: function () {
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/voter-stab-by-precinct/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'Stabs By Precinct No', 900, 600);
    },

    showOldPrintout: function () {
        var url = "http://" + window.hostIp + ":8100/voter-report/web/voter/attendance-old/index.php?event_id=" + this.props.eventId;
        this.popupCenter(url, 'List of Old Attendees', 900, 600);
    },

    popupCenter: function (url, title, w, h) {
        // Fixes dual-screen position                         Most browsers      Firefox  
        var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
        var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
        var width = 0;
        var height = 0;

        width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

        var left = ((width / 2) - (w / 2)) + dualScreenLeft;
        var top = ((height / 2) - (h / 2)) + dualScreenTop;
        var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

        // Puts focus on the newWindow  
        if (window.focus) {
            newWindow.focus();
        }
    }
});


window.KamadaPrelistingModal = KamadaPrelistingModal;