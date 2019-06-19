import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';
import ModalWrapper from './wrapper/ModalWrapper';
import ModalHeader from './wrapper/ModalHeader';
import ModalFooter from './wrapper/ModalFooter';
import Util from '../library/utils/Util';
import Basic from './include/BindOpAddAuth/Basic';
import Bearer from './include/BindOpAddAuth/Bearer';
/* global global_data */

@inject('modalStore', 'opsStore')
@observer
class BindOpAddAuth extends Component {
    constructor(props) {
        super(props);
        this.formEl = React.createRef();
        this.state = {
            params: [{}],
        };
    }
    changeVal = (key, index, value) => {
        this.setState(prevState => {
            prevState.params[index][key] = value;
            return {
                params: prevState.params,
            };
        });
    };

    addParam = () => {
        this.setState(prevState => {
            prevState.params.push({});
            return {
                params: prevState.params,
            };
        });
    };
    removeParam = index => {
        this.setState(prevState => {
            _.pullAt(prevState.params, index);
            return {
                params: prevState.params,
            };
        });
    };

    submit(e) {
        e.preventDefault();
        const { params } = this.state;
        const { op_id } = this.props.data;
        if (Util.formCheckRequired(this.formEl.current)) {
            let p = {};
            params.map(param => {
                _.forEach(param, function(value, key) {
                    if (typeof p[key] === 'undefined') {
                        p[key] = [];
                    }
                    p[key].push(value);
                });
            });

            this.props.opsStore.moveOperatorsByIndex('bind', [op_id], p);
        }

        // const { op } = this.props.data;
    }

    render() {
        const { dictionary } = global_data;
        const { params } = this.state;
        const { type } = this.props.data;
        const Component = type === 1 ? Basic : Bearer,
            l = params.length - 1;

        return (
            <ModalWrapper>
                <form
                    className="modal-content"
                    ref={this.formEl}
                    onSubmit={this.submit.bind(this)}>
                    <ModalHeader>
                        <i className="fa fa-lg fa-fw fa-puzzle-piece" />{' '}
                        <span>
                            Set Auth ({type === 1 ? 'Basic' : 'Bearer'})
                        </span>
                    </ModalHeader>
                    <div className="modal-body">
                        <div className="widget-body no-padding">
                            <div className="smart-form">
                                <fieldset className="fieldset-input">
                                    {params.map((p, i) => (
                                        <Component
                                            changeVal={this.changeVal}
                                            addParam={this.addParam}
                                            removeParam={this.removeParam}
                                            index={i}
                                            len={l}
                                            key={`bindOpaddComp_${i}`}
                                            {...p}
                                        />
                                    ))}
                                </fieldset>
                            </div>
                        </div>
                    </div>
                    <ModalFooter>
                        <button type="submit" className="btn btn-success">
                            <i className="fa fa-save" />{' '}
                            {dictionary.button.save}
                        </button>
                    </ModalFooter>
                </form>
            </ModalWrapper>
        );
    }
}
export default BindOpAddAuth;
