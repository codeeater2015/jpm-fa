var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var KamadaPrelistingAddAttendeeModal = React.createClass({

    getInitialState: function () {
        return {
            printMode: 'EVENT',
            profileEndpoint: "",
            municipalityName : "",
            brgyNo : "",
            unselected: [],
            options: [],
            form: {
                data: {
                    profiles: [],

                },
                errors: []
            }
        };
    },

    componentDidMount: function () {
        this.initSelect2();
        this.initMultiSelect();
    },

    initSelect2: function () {
        var self = this;

        $("#kamada_prelisting_add_attendee_form #pre-municipality-select2").select2({
            casesentitive: false,
            placeholder: "Enter municipality...",
            width: '100%',
            allowClear: true,
            tags: true,
            containerCssClass: ":all:",
            createTag: function (params) {
                return {
                    id: params.term,
                    text: params.term,
                    newOption: true
                }
            },
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

        
        $("#kamada_prelisting_add_attendee_form #pre-barangay-select2").select2({
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
                        municipalityNo: $("#kamada_prelisting_add_attendee_form #pre-municipality-select2").val()
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

        
        $("#kamada_prelisting_add_attendee_form #pre-barangay-select2").on("change", function () {
            console.log("loading profiles");
           self.loadProfiles();
        });
    },


    loadProfiles: function () {
        var self = this;
        var endpoint = Routing.generate("ajax_get_kamada_headers", {
            municipalityNo : $("#kamada_prelisting_add_attendee_form #pre-municipality-select2").val() , 
            brgyNo : $("#kamada_prelisting_add_attendee_form #pre-barangay-select2").val()
        });

        self.requestProfiles = $.ajax({
            url: endpoint,
            type: "GET"
        }).done(function (res) {
            self.setState({ options: res, unselected: res });
            console.log("kamada leaders has been received",res);
            setTimeout(self.refreshSelectBox, 2000);
        });
    },

    initMultiSelect: function () {
        var self = this;

        var selectBox = this.refs.selectBox;

        $(selectBox).multiSelect({
            selectableOptgroup: true,
            selectableHeader: "<input placeholder='Enter Name' type='text' class='form-control' autocomplete='off' style='text-transform:uppercase;margin-bottom:5px;'>",
            selectionHeader: "<input placeholder='Enter Name' type='text' class='form-control' autocomplete='off' style='text-transform:uppercase;margin-bottom:5px;'>",
            afterInit: function (ms) {
                var that = this,
                    $selectableSearch = that.$selectableUl.prev(),
                    $selectionSearch = that.$selectionUl.prev(),
                    selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
                    selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

                that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
                    .on('keydown', function (e) {
                        if (e.which === 40) {
                            that.$selectableUl.focus();
                            return false;
                        }
                    });

                that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
                    .on('keydown', function (e) {
                        if (e.which == 40) {
                            that.$selectionUl.focus();
                            return false;
                        }
                    });
            },

            afterSelect: function (values) {
                this.qs1.cache();
                this.qs2.cache();
                self.setProfiles($(self.refs.selectBox).val());
            },

            afterDeselect: function (values) {
                this.qs1.cache();
                this.qs2.cache();
                self.setProfiles($(self.refs.selectBox).val());
            },
            cssClass: "fluid-size"
        });


    },

    refreshSelectBox: function () {
        $(this.refs.selectBox).multiSelect('refresh');
    },

    deselectAll: function () {
        $(this.refs.selectBox).multiSelect('deselect_all');
    },

    selectAll: function () {
        $(this.refs.selectBox).multiSelect('select_all');
    },

    setProfiles: function (selected) {
        var form = this.state.form;
        var unselected = [];

        if (selected != null) {
            form.data.profiles = selected;
            unselected = this.state.options.filter(function (item) {
                return selected.indexOf(item.profile_no) == -1;
            });
        } else {
            form.data.profiles = [];
            unselected = this.state.options;
        }

        this.setState({ form: form, unselected: unselected });
    },

    reset: function () {
        var form = this.state.form;
        form.data.proVoterId = "";
        form.data.cellphone = "";
        form.data.remarks = "";

        form.errors = [];

        $("#form-voter-select2").empty().trigger("change");

        this.setState({ form: form });
    },


    handleCheckbox: function (e) {
        var form = this.state.form;

        form.data[e.target.name] = e.target.checked ? 1 : 0;

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

    submit: function (e) {
        e.preventDefault();
        var self = this;
        var data = self.state.form.data;

        self.requestTransmit = $.ajax({
            url: Routing.generate('ajax_post_kamada_prelisting_detail', { id: this.props.id }),
            type: 'POST',
            data: (data)
        }).done(function (res) {
            self.props.onSuccess();
            self.props.onHide();
        }).fail(function (res) {
            self.setErrors(res.responseJSON);
        });
    },

    render: function () {
        var self = this;
        var data = self.state.form.data;

        return (
            <Modal keyboard={false} enforceFocus={false} dialogClassName="modal-custom-85" backdrop="static" show={this.props.show} onHide={this.props.onHide}>
                <Modal.Header className="modal-header bg-blue-dark font-white" closeButton>
                    <Modal.Title>Add Attendees Form</Modal.Title>
                </Modal.Header>
                <Modal.Body bsClass="modal-body overflow-auto">
                    <form id="kamada_prelisting_add_attendee_form" onSubmit={this.submit}>
                        <div className="col-md-3 no-padding">
                            <FormGroup controlId="formBarangayNo">
                                <ControlLabel > Municipality : </ControlLabel>
                                <select id="pre-municipality-select2" className="form-control input-sm">
                                </select>
                            </FormGroup>
                        </div>
                        <div className="col-md-3">
                            <FormGroup controlId="formBarangayNo">
                                <ControlLabel > Barangay : </ControlLabel>
                                <select id="pre-barangay-select2" className="form-control input-sm">
                                </select>
                            </FormGroup>
                        </div>
                        <div className="clearfix"></div>
                        <div className="col-md-12 no-padding">
                            <div className="text-right">
                                <button type="button" onClick={this.deselectAll} className="btn btn-xs btn-default" style={{ marginRight: "5px" }}>Deselect All</button>
                                <button type="button" onClick={this.selectAll} className="btn btn-xs btn-success">Select All</button>
                            </div>
                        </div>
                        <div className="clearfix"></div>

                        <div className="col-md-6 no-padding">
                            <div><strong>Available :</strong> {this.state.unselected.length}</div>
                        </div>
                        <div className="col-md-6 ">
                            <div style={{ marginLeft: "32px" }}><strong>Selected : </strong> {this.state.form.data.profiles.length}</div>
                        </div>
                        <FormGroup controlId="formProfiles" validationState={this.getValidationState('profiles')} >
                            <select multiple ref="selectBox" className="searchable" id="contracts" name="profiles[]">
                                {this.state.options.map(function (item) {
                                    return (<option key={item.pro_voter_id} value={item.id}>{item.voter_name} ({item.voter_group}) - {item.barangay_name}</option>)
                                })}
                            </select>
                            <div className="text-right">
                                <HelpBlock>{this.getError('profiles')}</HelpBlock>
                            </div>
                        </FormGroup>
                        <div className="clearfix"></div>
                        <div className="text-right m-t-md">
                            <button type="button" className="btn btn-default" onClick={this.props.onHide}>Cancel</button>
                            <button type="submit" className="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </Modal.Body>
            </Modal>
        );
    }
});


window.KamadaPrelistingAddAttendeeModal = KamadaPrelistingAddAttendeeModal;