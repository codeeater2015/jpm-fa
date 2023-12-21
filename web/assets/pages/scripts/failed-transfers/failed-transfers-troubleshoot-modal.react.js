var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var FailedTransferTroubleshootModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    electId: 4,
                    proVoterId: null,
                    proIdCode: "",
                    recFormSub: 0,
                    houseFormSub: 0,
                    recFormSubCount: 0,
                    houseFormSubCount: 0,
                    recFormSubDate: "",
                    houseFormSubDate: "",
                    municipalityNo: "",
                    municipalityName: "",
                    barangayNo: "",
                    barangayName: ""
                },
                errors: []
            },
            provinceCode: 53,
            showNewVoterCreateModal: false
        };
    },

    render: function () {
        var self = this;
        var data = this.state.form.data;

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} bsSize="lg" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Troubleshoot</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">

                    <form id="member-status-create-form" onSubmit={this.submit}>
                        <div className="row">
                            <div className="col-md-3">
                                <FormGroup controlId="formMunicipalityNo" validationState={this.getValidationState('municipalityNo')}>
                                    <label className="control-label">City/Municipality</label>
                                    <select id="municipality_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                    </select>
                                    <HelpBlock>{this.getError('municipalityNo')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-3">
                                <FormGroup controlId="formBarangay" validationState={this.getValidationState('barangayNo')}>
                                    <label className="control-label">Barangay</label>
                                    <select id="barangay_select2" className="form-control form-filter input-sm" name="brgyNo">
                                    </select>
                                    <HelpBlock>{this.getError('barangayNo')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>

                        <div className="row">
                            <div className="col-md-8">
                                <FormGroup controlId="formVoterName" validationState={this.getValidationState('proVoterId')}>
                                    <ControlLabel > Name (Old Voterslist) : </ControlLabel>
                                    <select id="voter-recruit-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('proVoterId')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-4">
                                <button style={{ marginTop: "25px" }} onClick={this.voterNotFound} className="btn btn-primary btn-sm" type="button"> Not Found </button>
                            </div>
                            
                            <div className="clearfix"></div>

                            <div className="col-md-4">
                                <FormGroup controlId="formVoterName">
                                    <input type="text" value={this.state.form.data.voterName} className="input-sm form-control"  />
                                </FormGroup>
                            </div>

                        </div>

                        <br/>
                        <br/>

                        <div className="row">
                            <div className="col-md-12">
                                <FormGroup controlId="formTargetVoterName" validationState={this.getValidationState('targetProVoterId')}>
                                    <ControlLabel > Name (New Voterslist) : </ControlLabel>
                                    <select id="voter-recruit2-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('targetProVoterId')}</HelpBlock>
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

        $("#member-status-create-form #municipality_select2").select2({
            casesentitive: false,
            placeholder: "Select City/Municipality",
            allowClear: true,
            delay: 500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_municipality'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        provinceCode: self.state.provinceCode
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

        $("#member-status-create-form #barangay_select2").select2({
            casesentitive: false,
            placeholder: "Select Barangay",
            allowClear: true,
            delay: 500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_barangay'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        municipalityNo: $("#member-status-create-form #municipality_select2").val(),
                        provinceCode: self.state.provinceCode
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.brgy_no, text: item.name };
                        })
                    };
                },
            }
        });

        $("#member-status-create-form #voter-recruit-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 500,
            width: '100%',
            containerCssClass: ':all:',
            dropdownCssClass: 'custom-option',
            ajax: {
                url: Routing.generate('ajax_select2_failed_transfer_voters'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        municipalityNo: $("#member-status-create-form #municipality_select2").val(),
                        brgyNo: $("#member-status-create-form #barangay_select2").val()
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            var hasId = parseInt(item.has_id) == 1 ? "YES" : "NO";
                            var text = item.voter_name + ' ( ' + item.municipality_name + ', ' + item.barangay_name + " Prec # : " + item.precinct_no + ' ) - ID : ' + hasId;

                            return { id: item.pro_voter_id, text: text };
                        })
                    };
                },
            }
        });


        $("#member-status-create-form #voter-recruit2-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 500,
            width: '100%',
            containerCssClass: ':all:',
            dropdownCssClass: 'custom-option',
            ajax: {
                url: Routing.generate('ajax_select2_failed_transfer_new_list'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        municipalityNo: $("#member-status-create-form #municipality_select2").val()
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            var hasId = parseInt(item.has_id) == 1 ? "YES" : "NO";
                            var text = item.voter_name + ' ( ' + item.municipality_name + ', ' + item.barangay_name + " Prec # : " + item.precinct_no + ' )';

                            return { id: item.pro_voter_id, text: text };
                        })
                    };
                },
            }
        });

        $("#member-status-create-form #municipality_select2").on("change", function () {
            self.setFormPropValue('municipalityNo', $(this).val());
            self.loadMunicipality($(this).val());
        });

        $("#member-status-create-form #barangay_select2").on("change", function () {
            self.setFormPropValue('barangayNo', $(this).val());
            self.loadBarangay($("#member-status-create-form #municipality_select2").val(), $(this).val());
        });

        $("#member-status-create-form #voter-recruit-select2").on("change", function () {
            self.loadVoter(3, $(this).val());

            let form = self.state.form;
            form.data.proVoterId = $(this).val();

            self.setState({ form: form });
        });

        $("#member-status-create-form #voter-recruit2-select2").on("change", function () {
            let form = self.state.form;
            form.data.targetProVoterId = $(this).val();

            self.setState({ form: form });
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
            form.data.voterName = res.voterName;

            self.setState({ form: form });
        });

        var form = self.state.form;

        form.data.proVoterId = null;
        form.data.voterId = null;
        form.data.gender = '';
        form.data.remarks = '';

        self.setState({ form: form });
    },


    loadMunicipality: function (municipalityNo) {
        var self = this;
        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_municipality_loc", { municipalityNo: municipalityNo }),
            type: "GET"
        }).done(function (res) {
            var form = self.state.form;
            form.data.municipalityName = res.name;
            self.setState({ form: form });
        });
    },


    loadBarangay: function (municipalityNo, brgyNo) {
        var self = this;
        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_barangay_loc", { municipalityNo: municipalityNo, brgyNo: brgyNo }),
            type: "GET"
        }).done(function (res) {
            var form = self.state.form;
            form.data.barangayName = res.name;
            self.setState({ form: form });
        });
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

        form.data.proVoterId = null;
        form.data.targetProVoterId = "";
        form.data.voterName = "";

        $("#member-status-create-form #voter-recruit-select2")
            .empty()
            .trigger("change");

        $("#member-status-create-form #voter-recruit2-select2")
            .empty()
            .trigger("change");

        this.setState({ form: form });
    },

    voterNotFound: function(e){
        e.preventDefault();

        if(confirm("are you sure?")){

            var self = this;
            var data = self.state.form.data;
            data.proVoterId = $("#member-status-create-form #voter-recruit-select2").val();
            data.targetProVoterId = $("#member-status-create-form #voter-recruit2-select2").val();

            self.requestPost = $.ajax({
                url: Routing.generate("ajax_post_failed_transfer_not_found"),
                data: data,
                type: 'POST'
            }).done(function (res) {
                self.reset();
                self.props.reload();
            }).fail(function (err) {
                self.setErrors(err.responseJSON);
            });
        }
    },

    submit: function (e) {
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;
        data.proVoterId = $("#member-status-create-form #voter-recruit-select2").val();
        data.targetProVoterId = $("#member-status-create-form #voter-recruit2-select2").val();

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_post_transfer"),
            data: data,
            type: 'POST'
        }).done(function (res) {
            self.reset();
            self.props.reload();
        }).fail(function (err) {
            self.setErrors(err.responseJSON);
        });
    }

});


window.FailedTransferTroubleshootModal = FailedTransferTroubleshootModal;