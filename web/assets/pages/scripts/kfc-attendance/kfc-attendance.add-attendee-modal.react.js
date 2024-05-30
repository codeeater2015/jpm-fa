var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var KfcAttendanceAddAttendeeModal = React.createClass({

    getInitialState: function () {
        return {
            showNewProfileModal: false,
            showNewVoterCreateModal : false,
            form: {
                data: {
                    contactNo: "",
                    proVoterId: null
                },
                errors: []
            }
        };
    },


    openNewVoterCreateModal: function () {
        console.log('opening modal');
        this.setState({ showNewVoterCreateModal: true })
    },

    closeNewVoterCreateModal: function () {
        this.setState({ showNewVoterCreateModal: false });
    },

    render: function () {
        var self = this;
        var data = this.state.form.data;

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>New Attendee</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">

                {
                        this.state.showNewVoterCreateModal &&
                        <VoterTemporaryCreateModal
                            proId={3}
                            electId={423}
                            provinceCode={53}
                            show={this.state.showNewVoterCreateModal}
                            onHide={this.closeNewVoterCreateModal}
                        />
                    }

                    <form id="attendee-form" onSubmit={this.submit}>
                        <div className="row">
                            <div className="col-md-9">
                                <FormGroup controlId="formProVoterId" validationState={this.getValidationState('proVoterId')}>
                                    <ControlLabel >Name : </ControlLabel>
                                    <select id="voter-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('proVoterId')}</HelpBlock>
                                </FormGroup>
                            </div>
                            <div className="col-md-3">
                                <button style={{ marginTop: "25px" }} onClick={this.openNewVoterCreateModal} className="btn btn-primary btn-sm" type="button"> Non-voter </button>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-4" >
                                <FormGroup controlId="formContactNo" validationState={this.getValidationState('contactNo')}>
                                    <ControlLabel > Cellphone No: </ControlLabel>
                                    <input type="text" value={this.state.form.data.contactNo} name="contactNo" className="input-md form-control" onChange={this.setFormProp} />
                                    <HelpBlock>{this.getError('contactNo')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-12 text-right">
                                <button className="btn btn-primary btn-sm" style={{ marginRight: "10px" }} type="submit"> Submit </button>
                                <button className="btn btn-default btn-sm" type="button" onClick={this.props.onHide} > Close </button>
                            </div>
                        </div>

                    </form>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        this.initSelect2();
    },

    initSelect2: function () {
        var self = this;

        $("#attendee-form #voter-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            dropdownCssClass: 'custom-option',
            ajax: {
                url: Routing.generate('ajax_select2_project_voters'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        electId: 423,
                        proId: 3,
                        provinceCode: 53,
                        municipalityNo: $("#attendee-form #municipality_select2").val(),
                        brgyNo: self.props.brgyNo
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            var isVoter = item.is_non_voter == 1 ? "NO" : "YES";
                            var text = item.voter_name + ' ( ' + item.municipality_name + ', ' + item.barangay_name + ' ) - is voter? : ' + isVoter;

                            return { id: item.pro_voter_id, text: text };
                        })
                    };
                },
            }
        });

        $("#attendee-form #voter-select2").on("change", function () {
            self.loadVoter(3, $(this).val());
        });
    },

    loadVoter: function (proId, proVoterId) {
        var self = this;
        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter", { proId: proId, proVoterId: proVoterId }),
            type: "GET"
        }).done(function (res) {

            var form = self.state.form;

            form.data.proVoterId = res.proVoterId;
            form.data.contactNo = res.cellphone;

            self.setState({ form: form });
        });

        var form = self.state.form;

        form.data.proVoterId = null;
        form.data.contactNo = '';

        self.setState({ form: form })
    },


    setFormPropValue: function (field, value) {
        var form = this.state.form;
        form.data[field] = value;
        this.setState({ form: form });
    },

    setFormProp: function (e) {
        var form = this.state.form;
        form.data[e.target.name] = e.target.value;
        this.setState({ form: form });
    },

    setFormCheckProp: function (e) {
        var form = this.state.form;
        form.data[e.target.name] = e.target.checked ? 1 : 0;
        this.setState({ form: form })
    },

    setErrors: function (errors) {
        var form = this.state.form;
        form.errors = errors;
        this.setState({ form: form });
    },

    getError: function (field) {
        var errors = this.state.form.errors;
        for (var errorField in errors) {
            if (errorField == field)
                return errors[field];
        }
        return null;
    },

    getValidationState: function (field) {
        return this.getError(field) != null ? 'error' : '';
    },

    isEmpty: function (value) {
        return value == null || value == '';
    },

    reset: function () {
        var form = this.state.form;
        form.errors = [];

        form.data.contactNo = "";
        form.data.proVoterId = null;

        $("#attendee-form #voter-select2").empty().trigger("change");

        this.setState({ form: form });
    },

    openNewProfileModal: function () {
        var self = this;

        self.setState({ showNewProfileModal: true });
    },

    closeNewProfileModal: function () {
        var self = this;

        self.setState({ showNewProfileModal: false });
    },

    submit: function (e) {
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;

        console.log("form data");
        console.log(self.state.form.data);

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_post_kfc_attendance_detail", { id: this.props.id }),
            data: data,
            type: 'POST'
        }).done(function (res) {
            self.reset();
            self.props.onSuccess();
        }).fail(function (err) {
            self.setErrors(err.responseJSON);
        });
    }
});


window.KfcAttendanceAddAttendeeModal = KfcAttendanceAddAttendeeModal;