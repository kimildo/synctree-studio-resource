import React, { Component } from 'react';
import _ from 'lodash';
// http://projectstorm.cloud/react-diagrams/?selectedKind=Advanced%20Techniques&selectedStory=Large%20application&full=0&addons=1&stories=1&panelRight=1&addonPanel=storybook%2Fcode%2Fpanel
import * as SRD from 'storm-react-diagrams';
import { DefaultNodeModel, DiagramWidget } from 'storm-react-diagrams';

const jsonData = {
    app_id: '22',
    biz_id: 43,
    biz_uid: 'dd3373pEdde1Cy6yjpmQzy',
    biz_name: 'Calc_biz123',
    biz_desc: 'Calc_biz12',
    method: 2,
    actor_alias: 'Consumer',
    req_method: 'G',
    reg_date: '2019-02-11 15:12:54',
    request: [
        {
            param_id: 573,
            req_key: 'req_num1',
            req_var_type: 'JSN',
            req_desc: '',
            sub_parameter_format: {
                qf1212f: 'value',
                qweqweqwe: 'value',
            },
        },
        {
            param_id: 574,
            req_key: 'req_num2',
            req_var_type: 'INT',
            req_desc: 'asd',
            sub_parameter_format: null,
        },
        {
            param_id: 778,
            req_key: 'test4',
            req_var_type: 'JSN',
            req_desc: '',
            sub_parameter_format: null,
        },
    ],
    operators: {
        '1': {
            op_id: 59,
            op_text: 'multiply',
            binding_seq: 1,
            target_line_idx: 10,
            op_info: {
                op_id: 59,
                op_key: 'FnCQX5UQGsH5w11FQHEOew',
                op_name: 'multiply',
                op_ns_name: '121',
                op_desc: '121',
                method: 2,
                req_method: 'G',
                regist_date: '2019-02-11 15:18:42',
                modify_date: '2019-03-12 18:43:07',
                target_url:
                    'http://ec2-52-78-170-92.ap-northeast-2.compute.amazonaws.com/test/multiply',
                header_transfer_type_code: 1,
                auth_type_code: 0,
                request: [
                    {
                        param_id: 563,
                        param_seq: 1,
                        req_key: 'num1',
                        req_var_type: 'INT',
                        req_desc: '',
                        sub_parameter_format: null,
                    },
                    {
                        param_id: 564,
                        param_seq: 2,
                        req_key: 'num2',
                        req_var_type: 'INT',
                        req_desc: '',
                        sub_parameter_format: null,
                    },
                ],
                response: [
                    {
                        param_id: 565,
                        param_seq: 3,
                        res_key: 'result',
                        res_var_type: 'INT',
                        res_desc: '',
                        sub_parameter_format: null,
                    },
                    {
                        param_id: 566,
                        param_seq: 4,
                        res_key: 'data',
                        res_var_type: 'INT',
                        res_desc: '',
                        sub_parameter_format: null,
                    },
                    {
                        param_id: 779,
                        param_seq: 5,
                        res_key: 'test',
                        res_var_type: 'JSN',
                        res_desc: '',
                        sub_parameter_format: null,
                    },
                ],
            },
        },
        '2': {
            op_id: 60,
            op_text: 'square',
            binding_seq: 2,
            target_line_idx: 4,
            op_info: {
                op_id: 60,
                op_key: 'SrNTOa-iEC_h5krQMFSylw',
                op_name: 'square',
                op_ns_name: 'SQUARE',
                op_desc: '',
                method: 2,
                req_method: 'G',
                regist_date: '2019-02-11 15:20:32',
                modify_date: '2019-03-12 18:49:21',
                target_url:
                    'http://ec2-52-78-170-92.ap-northeast-2.compute.amazonaws.com/test/square',
                header_transfer_type_code: 1,
                auth_type_code: 0,
                request: [
                    {
                        param_id: 570,
                        param_seq: 1,
                        req_key: 'num',
                        req_var_type: 'INT',
                        req_desc: '',
                        sub_parameter_format: null,
                    },
                ],
                response: [
                    {
                        param_id: 571,
                        param_seq: 2,
                        res_key: 'result',
                        res_var_type: 'INT',
                        res_desc: '',
                        sub_parameter_format: null,
                    },
                    {
                        param_id: 572,
                        param_seq: 3,
                        res_key: 'data',
                        res_var_type: 'INT',
                        res_desc: '',
                        sub_parameter_format: null,
                    },
                    {
                        param_id: 780,
                        param_seq: 4,
                        res_key: 'test',
                        res_var_type: 'JSN',
                        res_desc: '',
                        sub_parameter_format: null,
                    },
                ],
            },
        },
    },
    lines: {
        '4': {
            line_idx: 4,
            line_title: 'SQUARE',
        },
        '10': {
            line_idx: 10,
            line_title: '121',
        },
    },
    get_command:
        'http://192.168.99.100/Gendd3373pEdde1Cy6yjpmQzy/secure/getCommand',
    end_point: '/Gendd3373pEdde1Cy6yjpmQzy',
    dev_end_point:
        'http://192.168.99.100/Gendd3373pEdde1Cy6yjpmQzy?req_num1={{req_num1}}&req_num2={{req_num2}}&test4={{test4}}',
    product_end_point_url:
        'https://company1.studio.synctreengine.com/Gendd3373pEdde1Cy6yjpmQzy',
    product_end_point:
        'https://company1.studio.synctreengine.com/Gendd3373pEdde1Cy6yjpmQzy?req_num1={{req_num1}}&req_num2={{req_num2}}&test4={{test4}}',
};

// https://github.com/projectstorm/react-diagrams/issues/164

const diagramEngine = new SRD.DiagramEngine();
const activeModel = new SRD.DiagramModel();

diagramEngine.installDefaultFactories();
diagramEngine.setDiagramModel(activeModel);
//3-A) create a default node
var node1 = new DefaultNodeModel('Node 1', 'rgb(0,192,255)');
let port = node1.addOutPort('Out');
node1.setPosition(100, 100);

//3-B) create another default node
var node2 = new DefaultNodeModel('Node 2', 'rgb(192,255,0)');
let port2 = node2.addInPort('In');
node2.setPosition(400, 100);

// link the ports
let link1 = port.link(port2);
// link1.setColor('#123456');
activeModel.addAll(node1, node2, link1);

class TrayItemWidget extends Component {
    constructor(props) {
        super(props);
        this.state = {};
    }

    render() {
        return (
            <div
                style={{ borderColor: this.props.color }}
                draggable={true}
                onDragStart={event => {
                    event.dataTransfer.setData(
                        'storm-diagram-node',
                        JSON.stringify(this.props.model)
                    );
                }}
                className="tray-item">
                {this.props.name}
            </div>
        );
    }
}

class TrayWidget extends Component {
    render() {
        return <div className="tray">{this.props.children}</div>;
    }
}

class BodyWidget extends Component {
    constructor(props) {
        super(props);
        this.state = {};
    }
    onDrop = e => {
        var data = JSON.parse(e.dataTransfer.getData('storm-diagram-node'));
        var nodesCount = _.keys(diagramEngine.getDiagramModel().getNodes())
            .length;
        console.log('onDrop fired', e.dataTransfer);

        var node = null;
        if (data.type === 'in') {
            node = new DefaultNodeModel(
                'Node ' + (nodesCount + 1),
                'rgb(192,255,0)'
            );
            node.addInPort('In');
        } else {
            node = new DefaultNodeModel(
                'Node ' + (nodesCount + 1),
                'rgb(0,192,255)'
            );
            node.addOutPort('Out');
        }
        var points = diagramEngine.getRelativeMousePoint(e);
        node.x = points.x;
        node.y = points.y;
        diagramEngine.getDiagramModel().addNode(node);
        this.forceUpdate();
    };
    onDragOver = e => {
        e.preventDefault();
    };

    render() {
        return (
            <div className="body">
                <div className="header">
                    <div className="title">Storm React Diagrams - Demo 5</div>
                </div>
                <div className="content">
                    <TrayWidget>
                        <TrayItemWidget
                            model={{ type: 'in' }}
                            name="Alt"
                            color="rgb(192,255,0)"
                        />
                        <TrayItemWidget
                            model={{ type: 'out' }}
                            name="Async"
                            color="rgb(0,192,255)"
                        />
                    </TrayWidget>
                    <div
                        className="diagram-layer"
                        onDrop={this.onDrop}
                        onDragOver={this.onDragOver}>
                        <DiagramWidget
                            className="srd-demo-canvas"
                            diagramEngine={diagramEngine}
                        />
                    </div>
                </div>
            </div>
        );
    }
}

class SvgTest extends Component {
    render() {
        return <BodyWidget />;
    }
}

export default SvgTest;
