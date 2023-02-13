var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var TupadCreateModal = React.createClass({

    getInitialState: function () {
        return {
            showNewProfileModal: false,
            form: {
                data: {
                    municipalityNo: "",
                    barangayNo: "",
                },
                errors: []
            }
        };
    },

    render: function () {
        var self = this;
        var data = this.state.form.data;

        return (
            <Modal style={{ marginTop: "10px" }} bsSize="lg" keyboard={false} enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>TUPAD : {this.props.serviceType} - {this.props.barangayName}, {this.props.municipalityName}</Modal.Title>
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

                            </div>
                        </div>

                        <div className="clearfix" />

                        <div className="text-right" style={{ marginTop: "30px" }}>
                            <button type="button" className="btn-lg btn-default" style={{ marginRight: "5px" }} onClick={this.props.onHide}>Close</button>
                            <button type="button" className="btn-lg btn-primary" onClick={this.submit}>Submit</button>
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


        $("#fa-form #municipality_select2").on("change", function () {
            self.setFormPropValue('municipalityNo', $(this).val());
        });

        $("#fa-form #barangay_select2").on("change", function () {
            self.setFormPropValue('sourceBarangay', $(this).val());
        });

        $("#fa-form #voter_select2").on("change", function () {
            self.loadVoter(3, $(this).val());
        });
     
        $("#fa-form #municipality_select2").empty()
            .append($("<option/>")
                .val(self.props.municipalityNo)
                .text(self.props.municipalityName))
            .trigger("change");

        $("#fa-form #barangay_select2").empty()
            .append($("<option/>")
                .val(self.props.barangayName)
                .text(self.props.barangayName))
            .trigger("change");

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
            form.data.sourceMunicipality = self.props.municipalityName;
            form.data.sourceBarangay = form.data.sourceBarangay == "" ? self.props.barangayName : form.data.sourceBarangay;
            form.data.bMunicipality = res.municipalityName;
            form.data.bBarangay = res.barangayName;
            form.data.bExtname = res.extname;
            form.data.isVoter = parseInt(res.isNonVoter) != 1 ? 1 : 0 ;
            form.data.serviceType = self.props.serviceType;
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
        data.proId = self.props.proId;

        console.log("form data");
        console.log(self.state.form.data);

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_tupad_post_transaction"),
            data: data,
            type: 'POST'
        }).done(function (res) {
            self.reset();
            $("#fa-form #voter_select2").empty().trigger("change");
            self.props.reload();
        }).fail(function (err) {
            self.setErrors(err.responseJSON);
        });
    }
});


window.TupadCreateModal = TupadCreateModal;