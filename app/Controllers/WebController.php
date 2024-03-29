<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Plugins\WebPublishModule\Controllers;

use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\Post;
use App\Utilities\ConfigUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class WebController extends Controller
{
    public function index(Request $request)
    {
        $authKey = ConfigHelper::fresnsConfigByItemKey('web_publish_module_auth_key');

        return view('WebPublishModule::admin.index', compact('authKey'));
    }

    public function update()
    {
        $fresnsConfigItems = [
            [
                'item_key' => 'web_publish_module_auth_key',
                'item_value' => Str::random(32),
                'item_type' => 'string',
                'is_multilingual' => 0,
                'is_api' => 0,
            ],
        ];

        ConfigUtility::changeFresnsConfigItems($fresnsConfigItems);

        CacheHelper::forgetFresnsConfigs('web_publish_module_auth_key');

        return $this->updateSuccess();
    }

    public function editor()
    {
        $authKey = ConfigHelper::fresnsConfigByItemKey('web_publish_module_auth_key');

        return view('WebPublishModule::editor', compact('authKey'));
    }

    public function webSubmit(Request $request)
    {
        $result = [
            'code' => 0,
            'message' => 'ok',
            'data' => null,
        ];

        // check auth key
        $authKey = ConfigHelper::fresnsConfigByItemKey('web_publish_module_auth_key');
        if (! $request->authKey || $request->authKey != $authKey) {
            $result['code'] = 35301;
            $result['message'] = ConfigUtility::getCodeMessage(35301);

            return Response::json($result);
        }

        // check uid
        $uid = $request->uid;
        if (! $uid) {
            $result['code'] = 31602;
            $result['message'] = ConfigUtility::getCodeMessage(31602);

            return Response::json($result);
        }

        // check group
        $groupId = PrimaryHelper::fresnsPrimaryId('group', $request->postGid);
        if ($request->postGid && ! $groupId) {
            $result['code'] = 37100;
            $result['message'] = ConfigUtility::getCodeMessage(37100);

            return Response::json($result);
        }

        // check content
        if (! $request->content) {
            $result['code'] = 38204;
            $result['message'] = ConfigUtility::getCodeMessage(38204);

            return Response::json($result);
        }

        $content = $request->content;

        // 是否需要替换关键词
        $isReplace = false;
        if ($isReplace) {
            $replaces = [
                '腾讯' => '#腾讯#',
                '阿里巴巴' => '#阿里巴巴#',
                '阿里云' => '#阿里云#',
                '字节跳动' => '#字节跳动#',
                '马化腾' => '#马化腾#',
                '马云' => '#马云#',
                '张一鸣' => '#张一鸣#',
                '小米' => '#小米#',
                '小红书' => '#小红书#',
                '美团' => '#美团#',
                '百度' => '#百度#',
                '京东' => '#京东#',
                '拼多多' => '#拼多多#',
                '快手' => '#快手#',
                'TikTok' => '#TikTok#',
                '抖音' => '#抖音#',
                '网易' => '#网易#',
                '微博' => '#微博#',
                '知乎' => '#知乎#',
                '华为' => '#华为#',
                '携程' => '#携程#',
                '科大讯飞' => '#科大讯飞#',
                '李彦宏' => '#李彦宏#',
                '乔布斯' => '#乔布斯#',
                '马斯克' => '#马斯克#',
                'Apple' => '#Apple#',
                '苹果' => '#Apple#',
                'Google' => '#Google#',
                '谷歌' => '#Google#',
                'Microsoft' => '#Microsoft#',
                '微软' => '#Microsoft#',
                'Amazon' => '#Amazon#',
                '亚马逊' => '#Amazon#',
                'Tesla' => '#Tesla#',
                '特斯拉' => '#Tesla#',
                'Twitter' => '#Twitter#',
                '推特' => '#Twitter#',
                'Facebook' => '#Facebook#',
                '脸书' => '#Facebook#',
                'Samsung' => '#Samsung#',
                '三星' => '#Samsung#',
                'YouTube' => '#YouTube#',
                'SpaceX' => '#SpaceX#',
                'Netflix' => '#Netflix#',
                'Intel' => '#Intel#',
                '因特尔' => '#Intel#',
                '比特币' => '#比特币#',
                'LinkedIn' => '#LinkedIn#',
                '领英' => '#LinkedIn#',
                'YCombinator' => '#YCombinator#',
            ];
            foreach ($replaces as $search => $replace) {
                // 使用正则表达式确保后面没有点字符
                $pattern = '/' . preg_quote($search, '/') . '(?!\.)/i';

                // 使用 preg_replace 替换一次
                $content = preg_replace($pattern, $replace, $content, 1);
            }
        }

        $wordBody = [
            'uid' => $uid,
            'type' => 1,
            'quotePid' => $request->quotePid,
            'gid' => $request->gid,
            'title' => $request->title,
            'content' => $content,
            'isMarkdown' => (bool) $request->isMarkdown,
            'commentPolicy' => $request->commentPolicy,
            'commentPrivate' => $request->commentPrivate,
            'gtid' => $request->gtid,
            'locationInfo' => $request->locationInfo,
            'archives' => $request->archives,
            'extends' => $request->extends,
            'requireReview' => false,
        ];

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->contentQuickPublish($wordBody);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->getErrorResponse();
        }

        // upload file
        if ($request->image) {
            $fileWordBody = [
                'usageType' => FileUsage::TYPE_POST,
                'platformId' => 4,
                'tableName' => 'posts',
                'tableColumn' => 'id',
                'tableId' => $fresnsResp->getData('id'),
                'tableKey' => null,
                'aid' => null,
                'uid' => $uid,
                'type' => File::TYPE_IMAGE,
                'moreJson' => null,
                'file' => $request->image,
            ];

            \FresnsCmdWord::plugin('Fresns')->uploadFile($fileWordBody);
        }

        if ($request->datetime) {
            $post = Post::where('id', $fresnsResp->getData('id'))->first();

            $post?->update([
                'created_at' => $request->datetime,
            ]);
        }

        return Response::json($result);
    }
}
