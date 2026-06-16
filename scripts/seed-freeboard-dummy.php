<?php
declare(strict_types=1);

require_once __DIR__ . '/../common/board.php';

function smartcms_seed_freeboard_dummy_posts(int $target_count = 30): array
{
    $board = smartcms_board_find('free');
    if (!$board || (string)($board['status'] ?? '') === 'disabled') {
        return ['ok' => false, 'message' => '자유게시판을 찾을 수 없습니다.', 'created' => 0];
    }

    $user_stmt = smartcms_db()->query(
        "SELECT id, email, name, nickname, role, level, status
         FROM " . smartcms_table('users') . "
         WHERE status = 'active'
         ORDER BY CASE WHEN role = 'admin' THEN 0 WHEN role = 'manager' THEN 1 ELSE 2 END, level DESC, id ASC
         LIMIT 1"
    );
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    if (!is_array($user)) {
        return ['ok' => false, 'message' => '더미 글을 작성할 사용자를 찾을 수 없습니다.', 'created' => 0];
    }

    $author_name = smartcms_user_display_name($user);
    $seed_user = $user;
    $seed_user['name'] = $author_name;

    $templates = [
        ['title' => '주말에 뭐 하셨어요?', 'content' => "주말 근황을 가볍게 남겨보는 글입니다.\n\n다들 쉬면서 충전 잘 하셨는지 궁금하네요."],
        ['title' => '오늘 날씨가 꽤 선선하네요', 'content' => "오전에는 덥다고 생각했는데 오후가 되니 바람이 좋아졌습니다.\n\n산책하기 딱 좋은 날씨라 잠깐 나갔다 왔어요."],
        ['title' => '부트스트랩 간격 조정 팁 공유', 'content' => "보드 레이아웃을 다듬을 때는 간격 통일이 생각보다 중요합니다.\n\n여백을 줄이거나 넓힐 때는 컴포넌트 전체 톤을 같이 보는 편이 좋습니다."],
        ['title' => '최근에 본 웹 디자인 트렌드', 'content' => "요즘은 카드 간격과 타이포그래피만 잘 잡아도 화면이 꽤 정돈되어 보입니다.\n\n과한 장식보다 정보 구조를 먼저 맞추는 편이 더 오래 갑니다."],
        ['title' => '자바스크립트 상태 관리가 어렵네요', 'content' => "작은 화면에서는 단순한 상태도 금방 복잡해 보입니다.\n\n처음부터 데이터 흐름을 짧게 유지하는 게 중요한 것 같습니다."],
        ['title' => '업로드 이미지 리사이징 테스트', 'content' => "이미지 업로드와 리사이징이 정상적으로 동작하는지 확인해보는 글입니다.\n\n본문과 썸네일 모두 자연스럽게 맞아야 보기 편합니다."],
        ['title' => '게시판 리스트 여백은 어느 정도가 좋을까요?', 'content' => "목록은 너무 빽빽하면 답답하고, 너무 넓으면 정보 밀도가 떨어집니다.\n\n적당한 여백을 찾는 게 생각보다 어렵습니다."],
        ['title' => '모바일에서 에디터가 안 보일 때', 'content' => "모바일 대응은 늘 마지막에 발목을 잡는 것 같습니다.\n\n특히 토글 UI가 있으면 초기 상태와 렌더 타이밍을 같이 봐야 합니다."],
        ['title' => '오늘의 커피 한 잔 기록', 'content' => "오후 작업 전에 커피 한 잔 마시고 집중을 다시 잡았습니다.\n\n작업 리듬을 만드는 데 작은 습관이 꽤 도움이 됩니다."],
        ['title' => '질문: 링크 필드는 어디까지 필요할까', 'content' => "게시글에 링크를 남길 일이 생각보다 많습니다.\n\n하나만 넣을지, 링크 1과 링크 2처럼 확장할지는 사용 패턴을 보는 게 맞습니다."],
        ['title' => '익숙한 화면도 조금씩 바꾸면 새롭다', 'content' => "자주 보던 화면도 버튼 위치나 제목 크기만 바뀌어도 느낌이 달라집니다.\n\n너무 급하게 바꾸기보다 톤을 맞추는 게 먼저입니다."],
        ['title' => '이동 복사 삭제 흐름 점검', 'content' => "게시판 다중 작업은 클릭 수가 많아질수록 실수하기 쉽습니다.\n\n모달 확인과 삭제 확인을 분리하면 좀 더 안전해 보입니다."],
        ['title' => '작성자 표시는 닉네임이 편하네요', 'content' => "실명보다 닉네임이 나오는 쪽이 커뮤니티 느낌이 더 납니다.\n\n전체 정책으로 제어하면 게시판마다 따로 맞추지 않아도 됩니다."],
        ['title' => '썸네일 크기 기준을 다시 봐야 할 듯', 'content' => "갤러리와 웹진은 같은 이미지라도 보이는 비율이 달라야 자연스럽습니다.\n\n게시판 너비에 맞춘 기준이 있으면 훨씬 정리돼 보입니다."],
        ['title' => '조회수와 다운로드 카운트 정책', 'content' => "클릭 한 번마다 숫자가 오르면 통계가 쉽게 왜곡됩니다.\n\n조회와 다운로드는 각각 다른 집계 기준이 있어야 덜 어색합니다."],
        ['title' => '본문 미리보기 두 줄 정도가 적당', 'content' => "목록에서 본문이 전혀 안 보이면 글의 분위기를 읽기 어렵습니다.\n\n제목 아래 짧은 미리보기가 있으면 훨씬 자연스럽습니다."],
        ['title' => '갤러리 카드 여백을 조금 줄여봤습니다', 'content' => "카드 여백은 줄이되 답답하지 않게 보이는 선을 찾는 중입니다.\n\n이미지와 텍스트 사이 균형이 잘 맞아야 합니다."],
        ['title' => '공지사항은 테이블형이 더 익숙합니다', 'content' => "공지와 일반 글은 같은 레이아웃보다 성격이 다르게 보이는 편이 좋습니다.\n\n테이블형 리스트는 정보가 빠르게 들어옵니다."],
        ['title' => '오늘은 검색 하이라이팅이 잘 보이네요', 'content' => "검색 결과에서 찾은 단어가 잘 드러나면 글을 훑기 편합니다.\n\n특히 긴 목록에서는 가독성 차이가 큽니다."],
        ['title' => '한 줄 로그도 나쁘지 않네요', 'content' => "이동이나 복사 기록을 한 줄로 보여주면 훨씬 덜 복잡합니다.\n\n대신 누가 어떤 게시판에서 왔는지는 바로 보여주는 게 좋습니다."],
        ['title' => '본문과 에디터 출력 차이 정리', 'content' => "에디터 본문은 보이는 대로 출력되고, 일반 텍스트는 줄바꿈과 기본 서식이 유지되면 좋습니다.\n\n둘을 분리해서 생각하면 구현이 깔끔해집니다."],
        ['title' => '유튜브 게시판도 꽤 재밌겠네요', 'content' => "URL만 넣어도 썸네일이 따라오면 목록에서 훨씬 눈에 잘 띕니다.\n\n상세에서는 바로 재생까지 이어지면 흐름이 좋습니다."],
        ['title' => '다중 파일 업로드가 있으면 편합니다', 'content' => "한 번에 여러 파일을 올릴 수 있으면 글 쓰는 속도가 빨라집니다.\n\n삭제도 함께 정리되면 관리가 쉬워집니다."],
        ['title' => '관리자 모드 설정은 어디에 둘까', 'content' => "보드별 설정보다 전체 정책으로 묶는 편이 유지보수에 유리할 때가 있습니다.\n\n특히 공통 규칙은 한 곳에서 바꾸는 게 좋습니다."],
        ['title' => '목록의 테이블 열 정렬 점검', 'content' => "선택, 번호, 제목 열은 왼쪽 기준이 맞아야 시선이 덜 흔들립니다.\n\n열 정렬이 조금만 틀어져도 전체가 어색해 보입니다."],
        ['title' => '그누보드 스타일이 왜 익숙한지', 'content' => "익숙한 UI는 복잡한 설명 없이도 바로 쓸 수 있다는 장점이 있습니다.\n\n기본 구조를 너무 벗어나지 않는 게 안정적입니다."],
        ['title' => '글쓰기 버튼 라벨은 단순한 게 좋다', 'content' => "저장, 수정, 삭제, 목록처럼 짧은 라벨이 더 직관적입니다.\n\n아이콘을 줄이면 화면도 한결 가벼워 보입니다."],
        ['title' => '본문 상단에 이미지가 나오면 좋겠어요', 'content' => "첨부 이미지가 본문 위에 먼저 보이면 내용을 이해하기 쉽습니다.\n\n특히 사진 위주의 글은 본문 진입이 부드러워집니다."],
        ['title' => '자유게시판 테스트용 마지막 글', 'content' => "더미 데이터가 충분히 쌓였는지 확인하는 마지막 샘플 글입니다.\n\n목록, 검색, 최신글, 카드형 출력 모두 점검하기 좋습니다."],
    ];

    $current_count_stmt = smartcms_db()->prepare(
        "SELECT COUNT(*)
         FROM " . smartcms_table('board_posts') . "
         WHERE board_id = :board_id AND is_hidden = 0"
    );
    $current_count_stmt->execute(['board_id' => (int)$board['id']]);
    $current_count = (int)$current_count_stmt->fetchColumn();

    $target_count = max(0, $target_count);
    $needed = max(0, $target_count - $current_count);
    if ($needed === 0) {
        return [
            'ok' => true,
            'message' => '자유게시판에 이미 충분한 글이 있습니다.',
            'created' => 0,
            'total' => $current_count,
        ];
    }

    $created = 0;
    foreach (array_slice($templates, 0, $needed) as $template) {
        $result = smartcms_board_create_post(
            $board,
            $seed_user,
            (string)$template['title'],
            '',
            '',
            (string)$template['content'],
            'text',
            false,
            false
        );

        if (!empty($result['ok'])) {
            $created++;
        }
    }

    return [
        'ok' => true,
        'message' => '자유게시판 더미 글 ' . $created . '개를 생성했습니다.',
        'created' => $created,
        'total' => $current_count + $created,
    ];
}

if (PHP_SAPI === 'cli') {
    try {
        $target_count = 30;
        foreach ($argv ?? [] as $argument) {
            if (preg_match('/^--count=(\d+)$/', (string)$argument, $matches) === 1) {
                $target_count = max(1, (int)$matches[1]);
            }
        }

        $result = smartcms_seed_freeboard_dummy_posts($target_count);
        fwrite(STDOUT, $result['message'] . PHP_EOL);
        fwrite(STDOUT, 'created=' . (int)($result['created'] ?? 0) . ', total=' . (int)($result['total'] ?? 0) . PHP_EOL);
        exit(0);
    } catch (Throwable $e) {
        fwrite(STDERR, $e->getMessage() . PHP_EOL);
        exit(1);
    }
}
