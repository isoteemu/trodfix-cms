{strip}
{* This is an hack to create "safer" email addresses *}
{if $_Text_Wiki_subject && $_Text_Wiki_text}
{mailto address=$_Text_Wiki_email encode="javascript" subject=$_Text_Wiki_subject text=$_Text_Wiki_text}
{elseif $_Text_Wiki_text}
{mailto address=$_Text_Wiki_email encode="javascript" text=$_Text_Wiki_text}
{elseif $_Text_Wiki_subject}
{mailto address=$_Text_Wiki_email encode="javascript" subject=$_Text_Wiki_subject}
{else}
{mailto address=$_Text_Wiki_email encode="javascript"}
{/if}
{/strip}