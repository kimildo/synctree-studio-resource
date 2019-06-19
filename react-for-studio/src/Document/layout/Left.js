import React, { Component } from 'react';
import _ from 'lodash';
import { inject, observer } from 'mobx-react';
import { toJS } from 'mobx';

import MenuGroup from '../include/menu/MenuGroup';

@inject('docsStore')
@observer
class Left extends Component {
    getOps = ops => {
        const returnData = [];
        _.forEach(ops, (opArr, key) => {
            const ops = _.flattenDepth(Object.values(toJS(opArr)), 1);
            // const ops = Object.values(toJS(opArr));
            //    console.log('Left ops', key, ops);

            const namespace = key.split('_')[1];
            returnData.push(
                <MenuGroup
                    key={`m_1_${key}`}
                    dKey={key}
                    namespace={namespace}
                    ops={ops}
                />
            );
        });

        return returnData;
    };
    render() {
        const data = this.props.docsStore.getData;

        return (
            <nav>
                <div className="navbar">
                    <p className="navtitle">
                        <a href={'#top'} style={{ color: '#282828' }}>
                            {data.biz_name} - STUDIO
                        </a>
                    </p>
                    <ul className="navKind">
                        <li>
                            <a href={'#request'} className="navSubTitle">
                                Request Method
                            </a>
                        </li>
                        <li>
                            <a href={'#ops'} className="navSubTitle">
                                Included Operators
                            </a>
                        </li>

                        {this.getOps(data.operators)}
                        <li className="folder data">
                            <span className={`Fpost`}>POST</span>
                            <a href={`#secureProtocol`}>Secure Protocol</a>
                        </li>
                        {/* <li>
                            <a href={'#response'} className="navSubTitle">
                                Response Method
                            </a>
                        </li> */}
                    </ul>
                </div>
            </nav>
        );
    }
}
export default Left;
