var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var TupadEditModal = React.createClass({

    getInitialState: function () {
        return {
            showNewProfileModal: false,
            showCreateModal : false,
            form: {
                data: {
                    municipalityNo: "",
                    barangayNo: "",
                    cellphoneNo: ""
                },
                errors: []
            }
        };
    },

    render: function () {
        var self = this;
        var data = this.state.form.data;

        console.log('data', data);

        return (
            <Modal style={{ marginTop: "10px" }} bsSize="lg" keyboard={false} enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Edit Assistance : {data.serviceType} - {data.sourceBarangay}, {data.sourceMunicipality}</Modal.Title>
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

                    {
                        this.state.showCreateModal &&
                        <TupadMemberCreateModal
                            proId={3}
                            electId={4}
                            provinceCode={53}
                            hdrId={this.props.trnId}
                            show={this.state.showCreateModal}
                            onHide={this.closeCreateModal}
                            reload={this.reload}
                        />
                    }

                    <form id="fa-form" >
                        <div className="row">
                            <div className="col-md-12">

                                <div className="row">
                                    <div className="col-md-6">
                                        <FormGroup controlId="formMunicipalityNo" validationState={this.getValidationState('municipalityNo')}>
                                            <ControlLabel> Municipality (filter sa pag search) : </ControlLabel>
                                            <select id="municipality_select2" className="form-control form-filter input-md" name="municipalityNo">
                                            </select>
                                            <HelpBlock>{this.getError('municipalityNo')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                    <div className="col-md-6">
                                        <FormGroup controlId="formBarangayNo" validationState={this.getValidationState('barangayNo')}>
                                            <ControlLabel> Barangay (based sa listahan) : </ControlLabel>
                                            <select id="barangay_select2" className="form-control form-filter input-md" name="barangayNo">
                                            </select>
                                            <HelpBlock>{this.getError('barangayNo')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                </div>

                                <div className="row">
                                    <div className="col-md-3" >
                                        <FormGroup controlId="formServiceType" validationState={this.getValidationState('serviceType')}>
                                            <ControlLabel > Type of Assistance : </ControlLabel>
                                            <input type="text" value={this.state.form.data.serviceType} className="input-md form-control" disabled={true} />
                                        </FormGroup>
                                    </div>
                                    <div className="col-md-3" >
                                        <FormGroup controlId="formSource" validationState={this.getValidationState('source')}>
                                            <ControlLabel > Source : </ControlLabel>
                                            <input type="text" value={this.state.form.data.source} className="input-md form-control" disabled={true} />
                                        </FormGroup>
                                    </div>
                                    <div className="col-md-3" >
                                        <FormGroup controlId="formReleaseDate" validationState={this.getValidationState('releaseDate')}>
                                            <ControlLabel > Release Date : </ControlLabel>
                                            <input type="date" value={this.state.form.data.releaseDate} className="input-md form-control" disabled={true} />
                                        </FormGroup>
                                    </div>
                                </div>

                                <div className="row">
                                    <div className="col-md-9">
                                        <FormGroup controlId="formVoterName" validationState={this.getValidationState('voterName')}>
                                            <ControlLabel > Beneficiary Name : </ControlLabel>
                                            <select id="voter_select2" className="form-control input-md">
                                            </select>
                                            <HelpBlock>{this.getError('voterName')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                    <div className="col-md-3">
                                        <button style={{ marginTop: "25px" }} onClick={self.openNewProfileModal} type="button" className="btn btn-primary">New Profile</button>
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-md-3" >
                                        <FormGroup controlId="formCellphoneNo" validationState={this.getValidationState('cellphoneNo')}>
                                            <ControlLabel > Cellphone No: </ControlLabel>
                                            <input type="text" value={this.state.form.data.cellphoneNo} name="cellphoneNo" className="input-md form-control" onChange={this.setFormProp} />
                                            <HelpBlock>{this.getError('cellphoneNo')}</HelpBlock>
                                        </FormGroup>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="clearfix" />

                        <div className="text-right" style={{ marginTop: "15px" }}>
                            <button type="button" className="btn btn-primary col-md-12" onClick={this.submit}>Update Transaction</button>
                        </div>

                        <br />

                        <div className="row" style={{ marginTop: "50px" }}>
                            <div className="col-md-12">
                                <button type="button" className="btn btn-primary" onClick={this.openCreateModal}>Add Member</button>
                            </div>
                            <div className="col-md-12">
                                <h4>Assistance Members : </h4>
                                <TupadMemberDatatable ref="memberDatatable" hdrId={this.props.trnId} />
                            </div>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        this.initSelect2();
        this.loadTransaction(this.props.trnId);
    },

    loadTransaction: function (trnId) {
        var self = this;

        self.requestUser = $.ajax({
            url: Routing.generate("ajax_get_tupad_transction", { trnId: trnId }),
            type: "GET"
        }).done(function (res) {
            console.log("transaction has been received");
            console.log(res);

            var form = self.state.form;

            form.data.source = res.source;
            form.data.releaseDate = res.releaseDate;
            form.data.serviceType = res.serviceType;
            form.data.cellphoneNo = res.cellphoneNo;
            form.data.sourceMunicipality = res.sourceMunicipality;
            form.data.sourceBarangay = res.sourceBarangay;
            form.data.cellphoneNo = res.cellphoneNo;

            $("#fa-form #voter_select2").empty()
                .append($("<option/>")
                    .val(res.proVoterId)
                    .text(res.bName))
                .trigger("change");

            $("#fa-form #municipality_select2").empty()
                .append($("<option/>")
                    .val(res.sourceMunicipality)
                    .text(res.sourceMunicipality))
                .trigger("change");

            $("#fa-form #barangay_select2").empty()
                .append($("<option/>")
                    .val(res.sourceBarangay)
                    .text(res.sourceBarangay))
                .trigger("change");
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

            form.data.proVoterId = res.proVoterId;
            form.data.proIdCode = res.proIdCode;
            form.data.generatedIdNo = res.generatedIdNo;
            form.data.bExtname = res.extname;
            form.data.isVoter = parseInt(res.isNonVoter) != 1 ? 1 : 0;

            self.setState({ form: form });
        });

        var form = self.state.form;

        form.data.applicantName = "";
        form.data.jpmIdNo = "";
        form.data.applicantProVoterId = "";

        self.setState({ form: form })
    },

    initSelect2: function () {
        var self = this;

        $("#fa-form #municipality_select2").select2({
            casesentitive: false,
            placeholder: "Select City/Municipality",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_municipality'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        provinceCode: 53
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.municipality_no, text: item.name };
                        })
                    };
                },
            }
        });

        $("#fa-form #barangay_select2").select2({
            casesentitive: false,
            placeholder: "Select Barangay",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_barangay'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        municipalityNo: $("#fa-form #municipality_select2").val(),
                        provinceCode: 53
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.name, text: item.name };
                        })
                    };
                },
            }
        });

        $("#fa-form #voter_select2").select2({
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
                        provinceCode: 53,
                        municipalityNo: $("#fa-form #municipality_select2").val()
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

        $("#fa-form #voter_select2").on("change", function () {
            self.loadVoter(3, $(this).val());
        });
    },


    reload: function () {
        this.refs.memberDatatable.reload();
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

    openCreateModal: function () {
        var self = this;

        self.setState({ showCreateModal: true });
    },

    closeCreateModal: function () {
        var self = this;

        self.setState({ showCreateModal: false });
    },

    submit: function (e) {
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;
        data.proId = self.props.proId;
        data.source = self.props.source;
        data.releaseDate = self.props.releaseDate;

        console.log("form data");
        console.log(self.state.form.data);

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_tupad_patch_transaction",{hdrId : this.props.trnId }),
            data: data,
            type: 'PATCH'
        }).done(function (res) {
            self.reset();
            $("#fa-form #voter_select2").empty().trigger("change");
            self.props.reload();
        }).fail(function (err) {
            self.setErrors(err.responseJSON);
        });
    }
});


window.TupadEditModal = TupadEditModal;