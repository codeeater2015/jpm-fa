var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var HierarchyItemSwapModal = React.createClass({

    getInitialState: function () {
        return {
            form: {
                data: {
                    voterId: null,
                    proVoterId: null,
                    newProVoter : null,
                    newVoterGroup : null,
                },
                errors: []
            }
        };
    },

    render: function () {
        var self = this;

        return (
            <Modal style={{ marginTop: "10px" }} keyboard={false} bsSize="md" enforceFocus={false} backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Swap KFC Members</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="hierachy_swap_form" onSubmit={this.submit}>
                        <div className="row">
                            <div className="col-md-12">
                                <FormGroup controlId="formCurrentProVoterId">
                                    <ControlLabel >Current : </ControlLabel>
                                    <select id="current-voter-swap-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('currentProVoterId')}</HelpBlock>
                                </FormGroup>
                            </div>
                            <div className="col-md-6">
                                <FormGroup controlId="formProVoterId">
                                    <ControlLabel >Position : </ControlLabel>
                                    <select id="current-voter-group-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('proVoterId')}</HelpBlock>
                                </FormGroup>
                            </div>
                            <div className="col-md-12">
                                <FormGroup controlId="formNewProVoterId">
                                    <ControlLabel >New  : </ControlLabel>
                                    <select id="new-voter-swap-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('newProVoterId')}</HelpBlock>
                                </FormGroup>
                            </div>
                            <div className="col-md-6">
                                <FormGroup controlId="formProVoterId">
                                    <ControlLabel >Position : </ControlLabel>
                                    <select id="new-voter-group-select2" className="form-control input-sm">
                                    </select>
                                    <HelpBlock>{this.getError('proVoterId')}</HelpBlock>
                                </FormGroup>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-12 text-right">
                                <button className="btn btn-default" type="button" onClick={this.props.onHide} > Close </button>
                                <button className="btn btn-primary" type="submit" style={{ marginLeft: "10px" }}> Submit </button>
                            </div>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    },

    componentDidMount: function () {
        this.initSelect2();
        this.loadData(this.props.proVoterId);
    },

    loadData: function (proVoterId) {
        var self = this;
        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_hierarchy_item", { proVoterId: proVoterId }),
            type: "GET"
        }).done(function (res) {
            console.log('update profile data has been received');
            console.log(res);
            var form = self.state.form;

            form.data = res;
            self.setState({ form: form }, self.reinitSelect2);
        });
    },

    initSelect2: function () {
        var self = this;

        $("#hierachy_swap_form #current-voter-swap-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            dropdownCssClass: 'custom-option',
            ajax: {
                url: Routing.generate('ajax_hierarchy_select2_project_voters'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        electId: 423,
                        proId: 3,
                        provinceCode: 53
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            var isVoter = item.is_non_voter == 1 ? "NV" : "V";
                            var profileLabel = (item.position == '' || item.position == null) ? "No Profile" : item.position;
                            var verifiedLabel = Number.parseInt(item.has_attended) == 1 ? "Verified" : "Unverified";

                            var text = item.voter_name + ' ( ' + item.municipality_name + ', ' + item.barangay_name + ' ) ' + isVoter + " | " + profileLabel + " | " + verifiedLabel;

                            return { id: item.pro_voter_id, text: text };
                        })
                    };
                },
            }
        });

        $("#hierachy_swap_form #new-voter-swap-select2").select2({
            casesentitive: false,
            placeholder: "Enter Name...",
            allowClear: true,
            delay: 1500,
            width: '100%',
            containerCssClass: ':all:',
            dropdownCssClass: 'custom-option',
            ajax: {
                url: Routing.generate('ajax_hierarchy_select2_project_voters'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        electId: 423,
                        proId: 3,
                        provinceCode: 53
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            var isVoter = item.is_non_voter == 1 ? "NV" : "V";
                            var profileLabel = (item.position == '' || item.position == null) ? "No Profile" : item.position;
                            var verifiedLabel = Number.parseInt(item.has_attended) == 1 ? "Verified" : "Unverified";

                            var text = item.voter_name + ' ( ' + item.municipality_name + ', ' + item.barangay_name + ' ) ' + isVoter + " | " + profileLabel + " | " + verifiedLabel;

                            return { id: item.pro_voter_id, text: text };
                        })
                    };
                },
            }
        });


        $("#hierachy_swap_form #current-voter-group-select2").select2({
            casesentitive: false,
            placeholder: "Enter Group",
            width: '100%',
            allowClear: true,
            tags: true,
            containerCssClass: ":all:",
            createTag: function (params) {
                return {
                    id: params.term.toUpperCase(),
                    text: params.term.toUpperCase(),
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_hierarchy_select2_voter_group'),
                data: function (params) {
                    return {
                        searchText: params.term, // search term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.voter_group, text: item.voter_group };
                        })
                    };
                },
            }
        });

        $("#hierachy_swap_form #new-voter-group-select2").select2({
            casesentitive: false,
            placeholder: "Enter Group",
            width: '100%',
            allowClear: true,
            tags: true,
            containerCssClass: ":all:",
            createTag: function (params) {
                return {
                    id: params.term.toUpperCase(),
                    text: params.term.toUpperCase(),
                    newOption: true
                }
            },
            ajax: {
                url: Routing.generate('ajax_hierarchy_select2_voter_group'),
                data: function (params) {
                    return {
                        searchText: params.term, // search term
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.voter_group, text: item.voter_group };
                        })
                    };
                },
            }
        });


        $("#hierachy_swap_form #current-voter-group-select2").on("change", function () {
            self.setFormPropValue("voterGroup", $(this).val());
        });
        
        $("#hierachy_swap_form #new-voter-group-select2").on("change", function () {
            self.setFormPropValue("newVoterGroup", $(this).val());
        });

        $("#hierachy_swap_form #new-voter-swap-select2").on("change", function () {
            self.setFormPropValue("newProVoterId", $(this).val());
        });
    },

    reinitSelect2: function () {

        var voterGroup = this.state.form.data.voterGroup;
        var currVoterName = this.state.form.data.voterName;
        var currProVoterId = this.state.form.data.proVoterId;

        $("#hierachy_swap_form #current-voter-group-select2").empty()
            .append($("<option />")
                .val(voterGroup)
                .text(voterGroup))
            .trigger("change");

        $("#hierachy_swap_form #new-voter-group-select2").empty()
            .append($("<option />")
                .val(voterGroup)
                .text(voterGroup))
            .trigger("change");

        $("#hierachy_swap_form #current-voter-swap-select2").empty()
            .append($("<option />")
                .val(currProVoterId)
                .text(currVoterName))
            .trigger("change");
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

    submit: function (e) {
        e.preventDefault();

        var self = this;
        var data = self.state.form.data;
        data.proId = self.props.proId;
        data.voterGroup = $('#current-voter-group-select2').val();
        data.newVoterGroup = $('#new-voter-group-select2').val();

        self.requestPost = $.ajax({
            url: Routing.generate("ajax_hierarchy_patch_swap_item", { proVoterId: self.props.proVoterId }),
            data: data,
            type: 'PATCH'
        }).done(function (res) {
            console.log('patch complete');
            self.props.onSuccess();
            self.props.onHide();
        }).fail(function (err) {
            self.setErrors(err.responseJSON);
        });
    }
});


window.HierarchyItemSwapModal = HierarchyItemSwapModal;