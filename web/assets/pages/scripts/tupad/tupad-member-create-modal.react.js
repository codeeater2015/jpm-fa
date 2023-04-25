var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var TupadMemberCreateModal = React.createClass({

    getInitialState: function () {
        return {
            showNewProfileModal: false,
            form: {
                data: {
                    municipalityNo: "",
                    barangayNo: "",
                    cellphoneNo : ""
                },
                errors: []
            }
        };
    },

    render: function () {
        var self = this;
        var data = this.state.form.data;

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Assistance Member </Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">

                    {
                        this.state.showNewProfileModal &&
                        <TupadCreateNewProfileModal
                            proId={3}
                            electId={4}
                            provinceCode={53}
                            show={this.state.showNewProfileModal}
                            onHide={this.closeNewProfileModal}
                            municipalityNo={this.props.municipalityNo}
                            municipalityName={this.props.municipalityName}
                            barangayNo={this.props.barangayNo}
                            barangayName={this.props.barangayName}
                        />
                    }

                    <form id="assistance-detail-form" >
                        <div className="row">
                            <div className="col-md-12">
                                <div className="row">
                                    <div className="col-md-10">
                                        <FormGroup controlId="formVoterName" validationState={this.getValidationState('voterName')}>
                                            <ControlLabel > Beneficiary Name : </ControlLabel>
                                            <select id="voter_select2" className="form-control input-md">
                                            </select>
                                            <HelpBlock>{this.getError('voterName')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                    <div className="col-md-2">
                                        <button style={{ marginTop: "25px" }} onClick={self.openNewProfileModal} type="button" className="btn btn-primary">New</button>
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-md-6" >
                                        <FormGroup controlId="formCellphoneNo" validationState={this.getValidationState('cellphoneNo')}>
                                            <ControlLabel > Cellphone No: </ControlLabel>
                                            <input type="text" value={this.state.form.data.cellphoneNo} name="cellphoneNo" className="input-md form-control"  onChange={this.setFormProp} />
                                            <HelpBlock>{this.getError('cellphoneNo')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="clearfix" />

                        <div className="text-right" style={{ marginTop: "30px" }}>
                            <button type="button" className="btn btn-default" style={{ marginRight: "5px" }} onClick={this.props.onHide}>Close</button>
                            <button type="button" className="btn btn-primary" onClick={this.submit}>Submit</button>
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


        $("#assistance-detail-form #voter_select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            dropdownCssClass: 'custom-option',
            ajax: {
                url: Routing.generate('ajax_select2_tupad_project_voters'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        electId: 4,
                        proId: 3,
                        provinceCode : 53
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            var hasPhoto = parseInt(item.has_photo) == 1 ? "YES" : "NO";
                            var text = item.voter_name + ' ( ' + item.municipality_name + ', ' + item.barangay_name + ' ) - PHOTO : ' + hasPhoto;

                            return { id: item.pro_voter_id, text: text };
                        })
                    };
                },
            }
        });

        $("#assistance-detail-form #voter_select2").on("change", function () {
            self.loadVoter(3, $(this).val());
        });

    },

    loadVoter: function (proId, proVoterId) {
        var self = this;
        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_tupad_project_voter", { proId: proId, proVoterId: proVoterId }),
            type: "GET"
        }).done(function (res) {

            var form = self.state.form;
            console.log('voter has been found');
            console.log(res);

            console.log("props");
            console.log(self.props.barangayName);
            console.log("form data");
            console.log(form.data.sourceBarangay);


            form.data.proVoterId = res.proVoterId;
            form.data.proIdCode = res.proIdCode;
            form.data.generatedIdNo = res.generatedIdNo;
            form.data.bMunicipality = res.municipalityName;
            form.data.bBarangay = res.barangayName;
            form.data.bExtname = res.extname;
            form.data.isVoter = parseInt(res.isNonVoter) != 1 ? 1 : 0;
            form.data.bStatus = res.isKalaban;
            form.data.bName = res.voterName;

            self.setState({ form: form });
        });

        var form = self.state.form;

        form.data.applicantName = "";
        form.data.jpmIdNo = "";
        form.data.applicantProVoterId = "";

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

        data.hdrId = self.props.hdrId;
        
        console.log("form data");
        console.log(self.state.form.data);

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_tupad_post_transaction_detail", {hdrId : this.props.hdrId }),
            data: data,
            type: 'POST'
        }).done(function (res) {
            console.log("transaction detail has been added.");
            self.reset();
            $("#assistance-detail-form #voter_select2").empty().trigger("change");
            self.props.reload();
        }).fail(function (err) {
            self.setErrors(err.responseJSON);
        });
    }
});


window.TupadMemberCreateModal = TupadMemberCreateModal;