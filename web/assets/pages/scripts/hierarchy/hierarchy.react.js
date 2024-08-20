
var Modal = ReactBootstrap.Modal;
var FormGroup = ReactBootstrap.FormGroup
var HelpBlock = ReactBootstrap.HelpBlock;
var ControlLabel = ReactBootstrap.ControlLabel;
var FormControl = ReactBootstrap.FormControl;

var Hierarchy = React.createClass({

    getInitialState: function () {
        return {
            showCreateModal: false,
            showEditModal: false,
            showSmsModal: false,
            showProfileModal: false,
            selectedItem: null,
            summary : {
                municipality_name : "",
                barangay_name : "",
                total_voter : 0,
                total_tl : 0,
                total_0 : 0,
                total_1 : 0,
                total_2 : 0,
                target_tl : 0,
                target_0 : 0,
                total_no_profile : 0
            },
            form: {
                data: {
                    leaderId: null,
                    assignedMunNo: null,
                    assignedBrgyNo: null,
                    assignedPurok: null,
                    voterGroup: null,
                    voterGroupFilter: null,
                    municipalityFilter: null,
                    barangayFilter: null,
                    voterNameFilter: null
                }
            }
        }
    },

    componentDidMount: function () {
        var self = this;

        $('#tree1').tree({
            dragAndDrop: true,
            autoOpen: true,
            onCreateLi: function (node, $li) {
                // Append a link to the jqtree-element div.
                // The link has an url '#node-[id]' and a data property 'node-id'.
                console.log(node.getLevel());

                let level = node.getLevel();
                let badgeColor = 'badge-light';

                switch (level) {
                    case 1:
                        badgeColor = 'badge-success';
                        break;
                    case 2:
                        badgeColor = 'badge-warning';
                        break;
                    case 3:
                        badgeColor = 'badge-danger';
                        break;
                    default:
                        badgeColor = 'badge-light';
                        break;
                }

                console.log("node name");
                console.log(node.name);

                let nameParts = node.name.split(":");

                console.log(nameParts);

                // 0 = position
                // 1 = name
                // 2 = municipality 
                // 3 = barangay 
                // 4 = household profile counts

                let customHtml = '<em><small style="padding-left:5px;"><span class="badge badge-pill ' + badgeColor + '"> level ' + level + '</span></small></em>'
                //let profileCounterHtml = '<small style="margin-top:3px;margin-left:5px;" class="badge badge-primary badge-pill"><span><i class="fa fa-user"></i><span>' + nameParts[4] + '</span></span></small>';

                //customHtml += profileCounterHtml;
                customHtml += '<a href="#node-' + node.id + '" class="btn btn-icon tree-delete" style="margin-top:0px;padding-top:0px;color:#e62044" data-node-id="' + node.id + '"><i data-node-id="' + node.id + '"class="fa fa-trash"></i></a>';



                $li.find('.jqtree-element').append(
                    customHtml
                );

                //$('#tree1').jstree('updateNode', node, nameParts[1])

            }
        });

        $('#tree1').on(
            'tree.move',
            function (event) {
                console.log('moved_node', event.move_info.moved_node);
                console.log('target_node', event.move_info.target_node);
                console.log('position', event.move_info.position);
                console.log('previous_parent', event.move_info.previous_parent);
                console.log("update item");

                let data = {
                    proVoterId: event.move_info.moved_node.id,
                    parentId: event.move_info.target_node.id,
                    nodeLevel: event.move_info.target_node.getLevel() + 1
                };

                if (data.parentId != null) {
                    self.requestPost = $.ajax({
                        url: Routing.generate("ajax_hierarchy_patch_item"),
                        data: data,
                        type: 'PATCH'
                    }).done(function (res) {
                        console.log("patch succeeded.");
                        let selectedItem = self.state.selectedItem;
                        selectedItem.proVoterId = data.proVoterId;

                        self.setState({ selectedItem: selectedItem }, self.openEditModal);
                    }).fail(function (err) {
                        self.setErrors(err.responseJSON);
                        console.log("ops! something went wrong");
                    });
                } else {
                    alert("State not saved. No target node was found.");
                }

            }
        );


        $('#tree1').on('click', '.tree-delete', function (e) {

            console.log('delete triggered');

            // Get the id from the 'node-id' data property
            var node_id = $(e.target).data('node-id');

            console.log('node id', node_id);

            // Get the node from the tree
            var node = $('#tree1').tree('getNodeById', node_id.toString());
            console.log('node', node);

            if (node) {
                if (node.getLevel() != 1) {
                    if (confirm("are you sure you want to continue deleting this entire branch?")) {
                        self.delete(node_id);
                    } else {
                        console.log('canceling');
                    }
                } else {
                    //self.delete(node_id);
                    alert("You dont have permission to remove level 1 branch");
                }
            }
        });

        $('#tree1').on(
            'tree.select',
            function (event) {
                if (event.node) {
                    // node was selected
                    var node = event.node;
                    var form = self.state.form;

                    //alert(node.name);
                    self.loadSelectedItem(node.id);
                    // form.data.leaderId = node.id;

                    // self.setState({ form : form });
                }
                else {
                    // event.node is null
                    // a node was deselected
                    // e.previous_node contains the deselected node
                    console.log("node deselected");
                    self.setState({ selectedItem: null })
                }
            }
        );

        this.initSelect2();
        this.loadSummaryData();
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


    openCreateModal: function () {
        console.log("open sms modal");
        this.setState({ showCreateModal: true });
        console.log(this.state.showCreateModal);
    },

    closeCreateModal: function () {
        this.setState({ showCreateModal: false });
        this.refs.bcbpDatatableRef.reload();
    },

    closeEditModal: function () {
        this.setState({ showEditModal: false });
    },

    onCreateSuccess: function () {
        this.refs.attendanceDatatable.reload();
        this.setState({ showCreateModal: false });
    },

    getError: function (field) {
        var errors = this.state.form.errors;
        for (var errorField in errors) {
            if (errorField == field)
                return errors[field];
        }
        return null;
    },

    initSelect2: function () {
        var self = this;

        $("#hierarchy_page #voter-select2").select2({
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

                            var text = item.voter_name + ' ( ' + item.municipality_name + ', ' + item.barangay_name + ' ) ' + isVoter + " | " + profileLabel;

                            return { id: item.pro_voter_id, text: text };
                        })
                    };
                },
            }
        });

        $("#hierarchy_page #voter-head-select2").select2({
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
                        provinceCode: 53,
                        municipalityNo: $("#hierarchy_page #municipality_filter_select2").val(),
                        brgyNo: $("#hierarchy_page #barangay_filter_select2").val(),
                        voterGroup: self.state.form.data.voterGroupFilter
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            var isVoter = item.is_non_voter == 1 ? "NO" : "YES";
                            var voterGroup = item.voter_group;

                            var text = item.voter_name + ' ( ' + item.municipality_name + ', ' + item.barangay_name + ' ) - is voter? : ' + isVoter + '||' + voterGroup;

                            return { id: item.pro_voter_id, text: text };
                        })
                    };
                },
            }
        });

        $("#hierarchy_page #voter-group-select2").select2({
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

        $("#hierarchy_page #voter-group-filter-select2").select2({
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

        $("#hierarchy_page #municipality_select2").select2({
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

        $("#hierarchy_page #barangay_select2").select2({
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
                        municipalityNo: $("#municipality_select2").val(),
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

        $("#hierarchy_page #purok_select2").select2({
            casesentitive: false,
            placeholder: "Enter Group",
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
                url: Routing.generate('ajax_hierarchy_select2_purok'),
                data: function (params) {
                    return {
                        searchText: params.term,
                        municipalityNo: $("#municipality_select2").val(),
                        brgyNo: $("#barangay_select2").val(),
                        provinceCode: 53
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.assigned_purok, text: item.assigned_purok };
                        })
                    };
                },
            }
        });

        $("#hierarchy_page #municipality_filter_select2").select2({
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

        $("#hierarchy_page #barangay_filter_select2").select2({
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
                        municipalityNo: $("#hierarchy_page #municipality_filter_select2").val(),
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

        var self = this;

        $("#hierarchy_page #voter-group-select2").on("change", function () {
            console.log("voter group has been selected");
            console.log($(this).val());
            var form = self.state.form;
            form.data.voterGroup = $(this).val();

            self.setState({ form: form });
        });

        $("#hierarchy_page #voter-group-filter-select2").on("change", function () {
            var form = self.state.form;
            form.data.voterGroupFilter = $(this).val();

            self.setState({ form: form }, self.loadHierarchyData);
        });

        $("#hierarchy_page #municipality_select2").on("change", function () {

            self.setFieldValue("assignedMunNo", $(this).val());
        });

        $("#hierarchy_page #barangay_select2").on("change", function () {
            self.setFieldValue("assignedBrgyNo", $(this).val());
        });

        $("#hierarchy_page #municipality_filter_select2").on("change", function () {
            var form = self.state.form;
            form.data.municipalityFilter = $(this).val();

            self.setState({ form: form }, self.loadHierarchyData);
        });

        $("#hierarchy_page #barangay_filter_select2").on("change", function () {
            var form = self.state.form;
            form.data.barangayFilter = $(this).val();

            self.setState({ form: form }, self.loadHierarchyData);
        });

        $("#hierarchy_page #purok_select2").on("change", function () {
            self.setFieldValue("assignedPurok", $(this).val());
        });

        $("#hierarchy_page #voter-select2").on("change", function () {
            self.loadVoter(3, $(this).val());
        });

        $("#hierarchy_page #voter-head-select2").on("change", function () {
            console.log("head has been selected");
            console.log("reloading tree");

            let form = self.state.form;
            form.data.leaderId = $(this).val();

            self.setState({ form: form }, self.loadHierarchyData)
        });

        $("#hierarchy_page #voter-group-filter-select2").empty()
            .append($("<option />")
                .val('TOP LEADER')
                .text('TOP LEADER'))
            .trigger("change");
    },


    loadHierarchyData: function () {
        var leaderId = this.state.form.data.leaderId;
        var voterGroupFilter = this.state.form.data.voterGroupFilter;
        var municipalityNo = this.state.form.data.municipalityFilter;
        var barangayNo = this.state.form.data.barangayFilter;

        var hierarchyRoute = Routing.generate("ajax_get_hierarchy_sample_data", {
            leaderId: leaderId,
            voterGroupFilter: voterGroupFilter,
            municipalityNo: municipalityNo,
            barangayNo: barangayNo
        });

        var self = this;
        self.requestHierarchyData = $.ajax({
            url: hierarchyRoute,
            type: "GET"
        }).done(function (res) {
            console.log("new data has been received");
            console.log(res);
            $('#tree1').tree("loadData", res);
        });

        self.loadSummaryData();
    },

    loadSummaryData: function () {
        var municipalityNo = this.state.form.data.municipalityFilter;
        var barangayNo = this.state.form.data.barangayFilter;

        var hierarchyRoute = Routing.generate("ajax_m_get_hierarchy_summary", {
            municipalityNo: municipalityNo,
            barangayNo: barangayNo
        });

        var self = this;
        self.requestHierarchyData = $.ajax({
            url: hierarchyRoute,
            type: "GET"
        }).done(function (res) {
            console.log("summary has been received");
            console.log(res);
            self.setState({ summary : res });
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
            console.log('voter recieved');

            console.log(form.data);

            self.setState({ form: form });
        });

        var form = self.state.form;

        form.data.proVoterId = null;
        form.data.contactNo = '';

        self.setState({ form: form })
    },

    loadSelectedItem: function (proVoterId) {
        var self = this;
        self.requestVoter = $.ajax({
            url: Routing.generate("ajax_get_hierarchy_item", { proVoterId: proVoterId }),
            type: "GET"
        }).done(function (res) {
            console.log('selected item has been received');
            console.log(res);
            self.setState({ selectedItem: res });
        });
    },

    setFieldValue: function (field, value) {
        var form = this.state.form;
        form.data[field] = value;
        this.setState({ form: form });
    },

    openEditModal: function () {
        this.setState({ showEditModal: true });
    },

    closeProfileModal: function () {
        this.setState({ showProfileModal: false });
    },

    onSuccessUpdate: function () {
        this.loadHierarchyData();
    },

    addItem: function () {
        console.log('adding item');
        let self = this;

        if (self.state.form.data.proVoterId != null) {
            let parentId = 0;
            let proceedAdd = true;

            if (self.state.selectedItem != null) {
                parentId = self.state.selectedItem.proVoterId;
            } else {
                proceedAdd = confirm("No parent node selected. Are you sure you want to add a root node?");
                parentId = 0;
            }

            console.log(parentId);


            if (proceedAdd) {

                parentId = parentId != null ? parentId : 0;
                var voterGroup = $('#hierarchy_page #voter-group-select2').val();

                parentId = voterGroup == 'TOP LEADER' ? 0 : parentId;

                if (parentId == 0) {
                    if (!confirm("Are you sure you want to add a root node?")) {
                        proceedAdd = false;
                    }
                }

                if (proceedAdd) {
                    let data = {
                        proVoterId: this.state.form.data.proVoterId,
                        parentId: parentId,
                        voterGroup: voterGroup,
                        assignedMunNo: this.state.form.data.assignedMunNo,
                        assignedBrgyNo: this.state.form.data.assignedBrgyNo,
                        assignedPurok: this.state.form.data.assignedPurok
                    };

                    console.log(data);

                    self.requestPost = $.ajax({
                        url: Routing.generate("ajax_hierarchy_post_item"),
                        data: data,
                        type: 'POST'
                    }).done(function (res) {
                        console.log("request succeeded.")
                        self.loadHierarchyData();
                        $("#hierarchy_page #voter-select2").empty().trigger("change");
                    }).fail(function (err) {

                        for (const [key, value] of Object.entries(err.responseJSON)) {
                            console.log(`Key: ${key}, Value: ${value}`);
                            self.notify(`${key} : ${value}`, "teal");
                        }
                        console.log("ops! something went wrong");
                    });
                }
            }
        } else {
            alert("Opps! Empty form!");
        }
    },

    delete: function (proVoterId) {
        var self = this;

        self.requestDeleteVoter = $.ajax({
            url: Routing.generate("ajax_delete_hierarchy_item", { proVoterId: proVoterId }),
            type: "DELETE"
        }).done(function (res) {
            console.log('item has been removed.');
            self.loadHierarchyData();
        }).fail(function (res) {
        });
    },

    openProfileModal: function () {
        if (this.state.selectedItem != null) {
            this.setState({ showProfileModal: true });
        }
    },

    numberWithCommas: function(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    },

    render: function () {
        var self = this;
        var selectedItem = this.state.selectedItem;
        var summary = this.state.summary;

        return (
            <div className="portlet light portlet-fit bordered">
                <div className="portlet-body" id="bcbp_component">
                    {
                        this.state.showEditModal &&
                        <HierarchyItemEditModal
                            show={this.state.showEditModal}
                            onHide={this.closeEditModal}
                            onSuccess={this.onSuccessUpdate}
                            proVoterId={this.state.selectedItem.proVoterId}
                        />
                    }
                
                    {
                        this.state.showProfileModal &&
                        <HierarchyProfileModal
                            show={this.state.showProfileModal}
                            onHide={this.closeProfileModal}
                            proVoterId={this.state.selectedItem.hh_pro_voter_id}
                            headerText={this.state.selectedItem.hh_voter_name}
                        />
                    }
                    <div className="row" id="hierarchy_page">
                        <div className="col-md-4">

                            <div className="row">
                                <div className="col-md-6">
                                    <FormGroup controlId="formMunicipality" >
                                        <select id="municipality_filter_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                        </select>
                                        <HelpBlock>{this.getError('municipalityNo')}</HelpBlock>
                                    </FormGroup>
                                </div>
                                <div className="col-md-6">
                                    <FormGroup controlId="formBarangay">
                                        <select id="barangay_filter_select2" className="form-control form-filter input-sm" name="brgyNo">
                                        </select>
                                        <HelpBlock>{this.getError('barangayNo')}</HelpBlock>
                                    </FormGroup>
                                </div>
                            </div>

                            <div className="row">
                                <div className="col-md-4">
                                    <FormGroup controlId="formProVoterId">
                                        <ControlLabel >Position Filter : </ControlLabel>
                                        <select id="voter-group-filter-select2" className="form-control input-sm">
                                        </select>
                                        <HelpBlock>{this.getError('proVoterId')}</HelpBlock>
                                    </FormGroup>
                                </div>
                            </div>
                            <div className="row">
                                <div className="col-md-12">
                                    <FormGroup controlId="formProVoterId">
                                        <ControlLabel >Name Filter: </ControlLabel>
                                        <select id="voter-head-select2" className="form-control input-sm">
                                        </select>
                                        <HelpBlock>{this.getError('proVoterId')}</HelpBlock>
                                    </FormGroup>
                                </div>
                            </div>

                            <div className="row">
                                <div className="col-md-12">
                                    <h3><strong>Add Member:</strong></h3>
                                </div>
                                <div className="col-md-6">
                                    <FormGroup controlId="formMunicipality" >
                                        <label className="control-label">Assigned City/Municipality</label>
                                        <select id="municipality_select2" className="form-control form-filter input-sm" name="municipalityNo">
                                        </select>
                                        <HelpBlock>{this.getError('municipalityNo')}</HelpBlock>
                                    </FormGroup>
                                </div>

                                <div className="col-md-6">
                                    <FormGroup controlId="formBarangay">
                                        <label className="control-label">Assigned Barangay</label>
                                        <select id="barangay_select2" className="form-control form-filter input-sm" name="brgyNo">
                                        </select>
                                        <HelpBlock>{this.getError('barangayNo')}</HelpBlock>
                                    </FormGroup>
                                </div>
                            </div>
                            <div className="row">
                                <div className="col-md-6">
                                    <FormGroup controlId="formAssignedPurok">
                                        <label className="control-label">Assigned Purok / Sitio </label>
                                        <select id="purok_select2" className="form-control form-filter input-sm" name="assignedPurok">
                                        </select>
                                        <HelpBlock>{this.getError('assignedPurok')}</HelpBlock>
                                    </FormGroup>
                                </div>
                                <div className="col-md-6">
                                    <FormGroup controlId="formProVoterId">
                                        <ControlLabel >Position : </ControlLabel>
                                        <select id="voter-group-select2" className="form-control input-sm">
                                        </select>
                                        <HelpBlock>{this.getError('proVoterId')}</HelpBlock>
                                    </FormGroup>
                                </div>
                            </div>
                            <div className="row">
                                <div className="col-md-12">
                                    <FormGroup controlId="formProVoterId">
                                        <ControlLabel >Name : </ControlLabel>
                                        <select id="voter-select2" className="form-control input-sm">
                                        </select>
                                        <HelpBlock>{this.getError('proVoterId')}</HelpBlock>
                                    </FormGroup>
                                </div>
                            </div>
                            <div className="row">
                                <div className="col-md-12">
                                    <button type="button" className="btn btn-success btn-lg" style={{ width: "100%", marginRight: "10px", marginTop: "26px" }} onClick={this.addItem}>Add Item</button>
                                </div>
                            </div>
                        </div>

                        <div className="col-md-6">
                            <div className="row">
                                <table className="table table-condensed table-bordered">
                                    <tbody style={{ backgroundColor: "#a4baeb" }}>
                                        <tr>
                                            <th colSpan="2" className="text-center">TL : {self.numberWithCommas(parseInt(summary.total_tl))} / {self.numberWithCommas(parseInt(summary.target_tl))}</th>
                                            <th colSpan="2" className="text-center">K0 : {self.numberWithCommas(parseInt(summary.total_k0))} / {self.numberWithCommas(parseInt(summary.target_0))}</th>
                                            <th colSpan="2" className="text-center">K1 : {self.numberWithCommas(parseInt(summary.total_k1))} / {self.numberWithCommas(parseInt(summary.target_0) * 4)}</th>
                                            <th colSpan="2" className="text-center">K2 : {self.numberWithCommas(parseInt(summary.total_k2))} / {self.numberWithCommas(parseInt(summary.target_0) * 24)}</th>
                                            <th rowSpan="2" className="text-center">No Profile : {self.numberWithCommas(parseInt(summary.total_no_profile))}</th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div className="row">
                                <div className="col-md-12">
                                    <div id="tree1"></div>
                                </div>
                            </div>
                        </div>
                        <div className="col-md-2">
                            <p className="text-center"><strong>ACTIVE BRANCH OVERVIEW</strong></p>
                            {selectedItem != null ? (
                                <div>
                                    <div >
                                        <a onClick={this.openProfileModal} style={{ marginLeft: "5px" }} href="#" className="btn btn-sm btn-success m-btn m-btn--icon m-btn--icon-only">
                                            <i className="fa fa-home"></i>
                                        </a>
                                        <a onClick={this.openEditModal} style={{ marginLeft: "5px" }} href="#" className="btn btn-sm btn-primary m-btn m-btn--icon m-btn--icon-only">
                                            <i className="fa fa-edit"></i>
                                        </a>
                                    </div>
                                    <br />
                                    <div className="text-center" style={{ fontSize: "1.2em", marginBottom: "10px", marginTop: "10px" }}><strong>{selectedItem.voterName} </strong></div>
                                    <div><strong><small> Registered Address : </small></strong> <br /> {selectedItem.municipalityName}, {selectedItem.barangayName}</div>
                                    <div style={{ marginBottom: "10px" }}><strong> <small>is Voter :</small> </strong> {selectedItem.isNonVoter == 1 ? "NO" : "YES"}</div>
                                    <div style={{ marginBottom: "10px" }}><strong><small>Assigned Address :</small></strong> <br /> {selectedItem.assignedMunicipality},  {selectedItem.assignedBarangay}, {selectedItem.assignedPurok}</div>

                                    <div><strong><small>Contact # :</small></strong>  {selectedItem.voter.cellphone} </div>
                                    <div><strong><small>Hierarchy Position :</small></strong> {selectedItem.voter.voterGroup} </div>
                                    <div><strong><small>HH Position :</small></strong> {selectedItem.voter.position} </div>
                                    <br />
                                    <div><strong><small>Total Household Members :</small></strong> {selectedItem.members.length} </div>
                                    <div><strong><small>Voting Strength :</small></strong> {selectedItem.votingStrength.totalVoter} / {selectedItem.votingStrength.householdSize} </div>
                                    <div><strong><small>Within District : </small></strong> {selectedItem.votingStrength.withinDistrict}</div>
                                    <div><strong><small>Outside District : </small></strong> {selectedItem.votingStrength.outsideDistrict}</div>
                                </div>
                            ) : ""}
                        </div>
                    </div>
                </div>
            </div>
        )
    }
});

setTimeout(function () {
    ReactDOM.render(
        <Hierarchy />,
        document.getElementById('page-container')
    );
}, 500);
