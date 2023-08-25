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
                'item_tag' => 'WebPublishModule',
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
        $group = PrimaryHelper::fresnsGroupIdByGid($request->postGid);
        if ($request->postGid && ! $group) {
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

        $wordBody = [
            'uid' => $uid,
            'type' => 1,
            'postQuotePid' => $request->postQuotePid,
            'postGid' => $request->postGid,
            'postTitle' => $request->postTitle,
            'postIsCommentDisabled' => $request->postIsCommentDisabled,
            'postIsCommentPrivate' => $request->postIsCommentPrivate,
            'content' => $request->content,
            'isMarkdown' => (bool) $request->isMarkdown,
            'map' => $request->map,
            'extends' => $request->extends,
            'archives' => $request->archives,
            'requireReview' => false,
        ];

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->contentQuickPublish($wordBody);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->errorResponse();
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
