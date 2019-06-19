import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { Link, withRouter } from 'react-router-dom';
import { Helmet } from 'react-helmet';
import Swiper from 'react-id-swiper';
import AppContent from './AppContent';
@inject('navStore', 'appsStore', 'modalStore')
@observer
class Apps extends Component {
    componentDidMount() {
        this.props.navStore.setNav(['Apps']);
    }
    getAppContents() {
        let apps = this.props.appsStore.getApps;
        if (apps) {
            return apps.map(app => {
                return (
                    <AppContent key={`AppContent_${app.app_id}`} app={app} />
                );
            });
        }
        return apps;
    }
    addApp() {
        this.props.modalStore.showModal({
            type: 'AddApp',
            data: {
                type: 'getData',
            },
        });
    }
    render() {
        const swiperParams = {
            effect: 'coverflow',
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: 'auto',
            loop: false,
            rebuildOnUpdate: true,
            coverflowEffect: {
                rotate: 50,
                stretch: 0,
                depth: 100,
                modifier: 1,
                slideShadows: true,
            },
            pagination: {
                el: '.swiper-pagination',
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            on: {
                init: function() {
                    $('.swiper-container').css('visibility', 'visible');
                },
            },
        };

        let appsLayer = this.getAppContents.bind(this).call();
        return (
            <section>
                <Helmet>
                    <title>Apps - Synctree Studio V2.0 </title>
                </Helmet>
                <div className="inbox-nav-bar no-content-padding">
                    <h1 className="page-title txt-color-blueDark hidden-tablet">
                        Apps
                    </h1>
                    <div className="inbox-checkbox-triggered pull-right title-btn-wrap">
                        <a
                            className="button-add-app btn-new-app icon"
                            href="javascript:void(0);"
                            onClick={this.addApp.bind(this)}>
                            New App
                        </a>
                    </div>
                </div>

                <Swiper {...swiperParams}>
                    {appsLayer}
                    <div className="swiper-slide">
                        <button
                            type="button"
                            onClick={this.addApp.bind(this)}
                            className="button-add-app"
                            title="create new app">
                            create new app
                        </button>
                    </div>
                </Swiper>
            </section>
        );
    }
}

export default withRouter(Apps);
