var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var KamadaMemberCreateModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    proVoterId: null,
                    cellphone: "",
                    voterGroup: "",
                    municipalityNo : "",
                    barangayNo : "",
                    batchNo : ""
                },
                errors: []
            }
        };
    },

    componentDidMount: function () {
        this.initSelect2();
    },

    initSelect2: function () {
        var self = this;

        $("#kamada-member-form #municipality_select2").select2({
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

        $("#kamada-member-form #barangay_select2").select2({
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
                        municipalityNo: $("#kamada-member-form #municipality_select2").val(),
                        provinceCode: 53
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

        $("#kamada-member-form #form-voter-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1000,
            width: '100%',
            containerCssClass: ':all:',
            dropdownCssClass: "custom-option",
            ajax: {
                url: Routing.generate('ajax_select2_project_voters'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        proId: self.props.proId,
                        electId: self.props.electId,
                        provinceCode: 53
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            var text = item.voter_name + ' - ' + item.precinct_no + ' ( ' + item.municipality_name + ', ' + item.barangay_name + ' )';
                            return { id: item.pro_voter_id, text: text };
                        })
                    };
                },
            }
        });

        
        $("#kamada-member-form #voter_group_select2").select2({
            casesentitive: false,
            placeholder: "Select Position",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            ajax: {
                url: Routing.generate('ajax_select2_voter_group'),
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.voter_group, text: item.voter_group };
                        })
                    };
                },
            }
        });

          
        $("#kamada-member-form #batch_select2").select2({
            casesentitive: false,
            placeholder: "Select Batch",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            tags: true,
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_select2_kamada_batch_no'),
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.batch_no, text: item.batch_no };
                        })
                    };
                },
            }
        });


        $("#form-voter-select2").on("change", function () {
            self.loadVoter(self.props.proId, $(this).val());
        });

        $("#kamada-member-form #municipality_select2").on("change", function () {
            self.setFieldValue("municipalityNo", $(this).val());
        });

        $("#kamada-member-form #barangay_select2").on("change", function () {
            self.setFieldValue("barangayNo", $(this).val());
        });

        $("#kamada-member-form #voter_group_select2").on("change", function () {
            self.setFieldValue("voterGroup", $(this).val());
        });

        $("#kamada-member-form #batch_select2").on("change", function () {
            self.setFieldValue("batchNo", $(this).val());
        });

        $("#kamada-member-form #municipality_select2").empty()
            .append($("<option/>")
                .val(this.props.municipalityNo)
                .text(this.props.municipalityName))
            .trigger("change");

        $("#kamada-member-form #barangay_select2").empty()
            .append($("<option/>")
                .val(this.props.barangayNo)
                .text(this.props.barangayName))
            .trigger("change");

    },

    loadVoter: function (proId, proVoterId) {
        var self = this;
        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_project_voter", { proId: proId, proVoterId: proVoterId }),
            type: "GET"
        }).done(function (res) {

            var form = self.state.form;
            form.data.proVoterId = res.proVoterId;
            form.data.cellphone = self.isEmpty(res.cellphoneNo) ? '' : res.cellphoneNo;
            form.data.voterGroup = res.voterGroup;

            $("#kamada-member-form #voter_group_select2").empty()
                .append($("<option/>")
                    .val(res.voterGroup)
                    .text(res.voterGroup))
                .trigger("change");

            self.setState({ form: form });
        });

        var form = self.state.form;

        form.data.proVoterId = null;
        form.data.cellphone = '';
        form.data.voterGroup = '';

        self.setState({ form: form })
    },

    reset: function () {
        var form = this.state.form;
        form.data.proVoterId = null;
        form.data.cellphone = '';
        form.data.voterGroup = '';

        form.errors = [];

        $("#kamada-member-form #voter_group_select2").empty().trigger("change");
        $("#kamada-member-form #form-voter-select2").empty().trigger("change");

        this.setState({ form: form });
    },

    setFormProp: function (e) {
        var form = this.state.form;
        form.data[e.target.name] = e.target.value;
        this.setState({ form: form });
    },

    setFieldValue: function (field, value) {
        var form = this.state.form;
        form.data[field] = value;
        this.setState({ form: form });
    },

    setNewProfile: function (data) {
        var self = this;

        $("#form-voter-select2").empty()
            .append($("<option/>")
                .val(data.proVoterId)
                .text(data.voterName))
            .trigger("change")
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


    notify: function (message, color) {
        $.notific8('zindex', 11500);
        $.notific8(message, {
            heading: 'System Message',
            color: color,
            life: 5000,
            verticalEdge: 'right',
            horizontalEdge: 'top',
        });
    },

    submit: function (e) {
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;

        data.hdrId = self.props.hdrId;
        data.proId = self.props.proId;
        data.electId = self.props.electId;

        self.requestAddAttendee = $.ajax({
            url: Routing.generate("ajax_post_kamada_detail"),
            type: "POST",
            data: data
        }).done(function (res) {
            self.reset();
            self.props.onSuccess();
            self.notify("Member has been added.", "teal");
        }).fail(function (err) {
            self.notify("Form Validation Failed.", "ruby");
            self.setErrors(err.responseJSON);
        });
    },

    closeNewVoterCreateModal: function () {
        this.setState({ showNewVoterCreateModal: false });
    },

    openNewVoterCreateModal: function () {
        this.setState({ showNewVoterCreateModal: true })
    },

    render: function () {
        var self = this;
        var data = self.state.form.data;

        return (
            <Modal keyboard={false} enforceFocus={false} bsSize="lg" backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Household Member Form</Modal.Title>
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
                            onSuccess={this.setNewProfile}

                            municipalityNo={this.props.municipalityNo}
                            municipalityName={this.props.municipalityName}
                            barangayNo={this.props.barangayNo}
                            barangayName={this.props.barangayName}
                        />
                    }

                    <form id="kamada-member-form" onSubmit={this.submit}>
                        <div className="row">
                            <div className="col-md-3">
                                <FormGroup controlId="formMunicipalityNo" validationState={this.getValidationState('municipalityNo')}>
                                    <ControlLabel > Municipality : </ControlLabel>
                                    <select id="municipality_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                    </select>
                                    <HelpBlock>{this.getError('municipalityNo')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-3">
                                <FormGroup controlId="formBrgyNo" validationState={this.getValidationState('barangayNo')}>
                                    <ControlLabel > Barangay : </ControlLabel>
                                    <select id="barangay_select2" className="form-control form-filter input-sm" name="brgyNo">
                                    </select>
                                    <HelpBlock>{this.getError('barangayNo')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>

                        <div className="row">
                            <div className="col-md-8">
                                <FormGroup controlId="formProVoterId" validationState={this.getValidationState('proVoterId')}>
                                    <ControlLabel > Member Name : </ControlLabel>
                                    <select id="form-voter-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('proVoterId')}</HelpBlock>
                                </FormGroup>
                            </div>

                            <div className="col-md-2">
                                <button style={{ marginTop: "25px" }} onClick={this.openNewVoterCreateModal} className="btn btn-primary btn-sm" type="button"> New Voter </button>
                            </div>
                        </div>


                        <div className="row">
                            <div className="col-md-3" >
                                <FormGroup controlId="formCellphone" validationState={this.getValidationState('cellphone')}>
                                    <ControlLabel > Cellphone No : </ControlLabel>
                                    <input type="text" placeholder="Example : 09283182013" value={this.state.form.data.cellphone} className="input-sm form-control" onChange={this.setFormProp} name="cellphone" />
                                    <HelpBlock>{this.getError('cellphone')}</HelpBlock>
                                </FormGroup>
                            </div>
                            <div className="col-md-3">
                                <FormGroup controlId="formBatch" validationState={this.getValidationState('voterGroup')}>
                                    <ControlLabel > Position : </ControlLabel>
                                    <select id="voter_group_select2" className="form-control form-filter input-sm" name="batchNo">
                                    </select>
                                    <HelpBlock>{this.getError('voterGroup')}</HelpBlock>
                                </FormGroup>
                            </div>
                            <div className="col-md-3">
                                <FormGroup controlId="formBatch" validationState={this.getValidationState('batchNo')}>
                                    <ControlLabel > Batch No. : </ControlLabel>
                                    <select id="batch_select2" className="form-control form-filter input-sm" name="batchNo">
                                    </select>
                                    <HelpBlock>{this.getError('batchNo')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-12 text-right">
                                <button className="btn btn-primary btn-sm" disabled={this.isEmpty(this.state.form.data.proVoterId)} type="submit"> Submit </button>
                                <button className="btn btn-default btn-sm" type="button" onClick={this.props.onHide} > Close </button>
                            </div>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    }
});


window.KamadaMemberCreateModal = KamadaMemberCreateModal;